<?php

namespace Modules\CostCenterManagement\Services;

use Modules\CostCenterManagement\Models\CostPool;
use Modules\CostCenterManagement\Models\CostPoolMember;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CostPoolService
{
    /**
     * Accumulate costs from contributor cost centers to the pool
     * 
     * @param CostPool $pool
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return float Total accumulated cost
     */
    public function accumulateCosts(CostPool $pool, Carbon $periodStart, Carbon $periodEnd): float
    {
        if (!$pool->is_active) {
            throw new \Exception("Cost pool {$pool->code} is not active");
        }

        // Get all contributor cost centers
        $contributors = $pool->contributors()->with('costCenter')->get();

        if ($contributors->isEmpty()) {
            throw new \Exception("Cost pool {$pool->code} has no contributors");
        }

        $totalCost = 0;

        foreach ($contributors as $contributor) {
            if (!$contributor->costCenter->is_active) {
                continue; // Skip inactive cost centers
            }

            // Get cost for this cost center in the period
            // In real implementation, this would query cost_center_transactions
            $costCenterCost = $this->getCostCenterCostForPeriod(
                $contributor->cost_center_id,
                $periodStart,
                $periodEnd
            );

            $totalCost += $costCenterCost;
        }

        return $totalCost;
    }

    /**
     * Allocate pool costs to target cost centers
     * 
     * @param CostPool $pool
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return string Batch ID
     */
    public function allocatePool(CostPool $pool, Carbon $periodStart, Carbon $periodEnd): string
    {
        $batchId = 'POOL-' . now()->format('YmdHis') . '-' . Str::random(6);

        DB::beginTransaction();
        try {
            // Validate pool allocation rule
            $this->validatePoolAllocationRule($pool);

            // Accumulate costs from contributors
            $totalPoolCost = $this->accumulateCosts($pool, $periodStart, $periodEnd);

            if ($totalPoolCost <= 0) {
                throw new \Exception("No costs to allocate for pool {$pool->code}");
            }

            // Get target cost centers
            $targets = $pool->targets()->with('costCenter')->get();

            if ($targets->isEmpty()) {
                throw new \Exception("Cost pool {$pool->code} has no targets");
            }

            // Calculate allocation amounts based on allocation_base
            $allocations = $this->calculatePoolAllocations($pool, $targets, $totalPoolCost);

            // Create allocation journals
            $journals = [];
            $contributors = $pool->contributors()->with('costCenter')->get();
            
            // For simplicity, we'll use the first contributor as source
            // In real implementation, you might want to create separate journals for each contributor
            $sourceCostCenterId = $contributors->first()->cost_center_id;

            foreach ($allocations as $targetCostCenterId => $allocation) {
                $journals[] = [
                    'batch_id' => $batchId,
                    'allocation_rule_id' => null, // Pool allocation doesn't use allocation rules
                    'source_cost_center_id' => $sourceCostCenterId,
                    'target_cost_center_id' => $targetCostCenterId,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'source_amount' => $totalPoolCost,
                    'allocated_amount' => $allocation['amount'],
                    'allocation_base_value' => $allocation['base_value'],
                    'calculation_detail' => json_encode([
                        'method' => 'cost_pool',
                        'pool_id' => $pool->id,
                        'pool_code' => $pool->code,
                        'pool_type' => $pool->pool_type,
                        'allocation_base' => $pool->allocation_base,
                        'calculation' => $allocation['calculation'],
                    ]),
                    'status' => 'draft',
                    'created_at' => now(),
                ];
            }

            // Insert all journals
            if (!empty($journals)) {
                AllocationJournal::insert($journals);
            }

            // Validate zero-sum
            $journalCollection = AllocationJournal::where('batch_id', $batchId)->get();
            $totalAllocated = $journalCollection->sum('allocated_amount');
            $difference = abs($totalPoolCost - $totalAllocated);
            
            if ($difference > 0.01) {
                throw new \Exception("Zero-sum validation failed: difference = {$difference}");
            }

            DB::commit();
            return $batchId;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate pool allocation rule
     * 
     * @param CostPool $pool
     * @return bool
     * @throws \Exception
     */
    public function validatePoolAllocationRule(CostPool $pool): bool
    {
        if (!$pool->is_active) {
            throw new \Exception("Cost pool {$pool->code} is not active");
        }

        // Check if pool has contributors
        $contributorsCount = $pool->contributors()->count();
        if ($contributorsCount === 0) {
            throw new \Exception("Cost pool {$pool->code} has no contributors");
        }

        // Check if pool has targets
        $targetsCount = $pool->targets()->count();
        if ($targetsCount === 0) {
            throw new \Exception("Cost pool {$pool->code} has no targets");
        }

        // Check if all contributors are active
        $inactiveContributors = $pool->contributors()
            ->whereHas('costCenter', function ($query) {
                $query->where('is_active', false);
            })
            ->count();

        if ($inactiveContributors > 0) {
            throw new \Exception("Cost pool {$pool->code} has inactive contributor cost centers");
        }

        // Check if all targets are active
        $inactiveTargets = $pool->targets()
            ->whereHas('costCenter', function ($query) {
                $query->where('is_active', false);
            })
            ->count();

        if ($inactiveTargets > 0) {
            throw new \Exception("Cost pool {$pool->code} has inactive target cost centers");
        }

        return true;
    }

    /**
     * Get pool balance as of a specific date
     * 
     * @param CostPool $pool
     * @param Carbon $asOfDate
     * @return float
     */
    public function getPoolBalance(CostPool $pool, Carbon $asOfDate): float
    {
        // Get accumulated costs up to the date
        $periodStart = Carbon::parse($asOfDate)->startOfMonth();
        $periodEnd = Carbon::parse($asOfDate)->endOfMonth();

        return $this->accumulateCosts($pool, $periodStart, $periodEnd);
    }

    /**
     * Calculate pool allocations based on allocation_base
     * 
     * @param CostPool $pool
     * @param Collection $targets
     * @param float $totalPoolCost
     * @return array
     */
    protected function calculatePoolAllocations(CostPool $pool, Collection $targets, float $totalPoolCost): array
    {
        $allocations = [];

        switch ($pool->allocation_base) {
            case 'equal':
                // Equal distribution
                $count = $targets->count();
                $amountPerTarget = round($totalPoolCost / $count, 2);
                
                foreach ($targets as $target) {
                    $allocations[$target->cost_center_id] = [
                        'amount' => $amountPerTarget,
                        'base_value' => 1,
                        'calculation' => "{$totalPoolCost} / {$count}",
                    ];
                }
                break;

            case 'square_footage':
            case 'headcount':
            case 'service_volume':
            case 'revenue':
                // Weighted allocation based on base values
                // In real implementation, these values would come from actual data
                $baseValues = $this->getBaseValues($pool->allocation_base, $targets);
                $totalBaseValue = array_sum($baseValues);

                if ($totalBaseValue <= 0) {
                    throw new \Exception("Total base value is zero for allocation base {$pool->allocation_base}");
                }

                foreach ($targets as $target) {
                    $baseValue = $baseValues[$target->cost_center_id] ?? 0;
                    $amount = round($totalPoolCost * ($baseValue / $totalBaseValue), 2);
                    
                    $allocations[$target->cost_center_id] = [
                        'amount' => $amount,
                        'base_value' => $baseValue,
                        'calculation' => "{$totalPoolCost} * ({$baseValue} / {$totalBaseValue})",
                    ];
                }
                break;

            default:
                throw new \Exception("Unsupported allocation base: {$pool->allocation_base}");
        }

        // Adjust for rounding differences to ensure zero-sum
        $totalAllocated = array_sum(array_column($allocations, 'amount'));
        $difference = $totalPoolCost - $totalAllocated;
        
        if (abs($difference) > 0.001) {
            // Add difference to first target
            $firstTargetId = array_key_first($allocations);
            $allocations[$firstTargetId]['amount'] = round($allocations[$firstTargetId]['amount'] + $difference, 2);
            $allocations[$firstTargetId]['rounding_adjustment'] = $difference;
        }

        return $allocations;
    }

    /**
     * Get base values for allocation (placeholder)
     * In real implementation, this would query actual data
     * 
     * @param string $allocationBase
     * @param Collection $targets
     * @return array [cost_center_id => base_value]
     */
    protected function getBaseValues(string $allocationBase, Collection $targets): array
    {
        $baseValues = [];

        foreach ($targets as $target) {
            // Placeholder - return random values for testing
            // In production, query actual data based on allocation_base
            switch ($allocationBase) {
                case 'square_footage':
                    $baseValues[$target->cost_center_id] = rand(100, 1000); // Square meters
                    break;
                case 'headcount':
                    $baseValues[$target->cost_center_id] = rand(5, 50); // Number of employees
                    break;
                case 'service_volume':
                    $baseValues[$target->cost_center_id] = rand(100, 5000); // Number of services
                    break;
                case 'revenue':
                    $baseValues[$target->cost_center_id] = rand(1000000, 10000000); // Revenue amount
                    break;
                default:
                    $baseValues[$target->cost_center_id] = 1;
            }
        }

        return $baseValues;
    }

    /**
     * Get cost center cost for period (placeholder)
     * In real implementation, this would query cost_center_transactions
     * 
     * @param int $costCenterId
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return float
     */
    protected function getCostCenterCostForPeriod(int $costCenterId, Carbon $periodStart, Carbon $periodEnd): float
    {
        // Placeholder - return random cost for testing
        // In production, query cost_center_transactions table
        return rand(100000, 500000);
    }
}
