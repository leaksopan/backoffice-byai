<?php

namespace Modules\CostCenterManagement\Services;

use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Events\AllocationCompleted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CostAllocationService
{
    /**
     * Validate allocation rule
     * 
     * @param AllocationRule $rule
     * @return bool
     * @throws \Exception
     */
    public function validateAllocationRule(AllocationRule $rule): bool
    {
        // Check if rule is active and approved
        if (!$rule->is_active) {
            throw new \Exception("Allocation rule {$rule->code} is not active");
        }

        if ($rule->approval_status !== 'approved') {
            throw new \Exception("Allocation rule {$rule->code} is not approved");
        }

        // Check if source cost center is active
        if (!$rule->sourceCostCenter->is_active) {
            throw new \Exception("Source cost center {$rule->sourceCostCenter->code} is not active");
        }

        // Load targets
        $targets = $rule->targets()->with('targetCostCenter')->get();

        if ($targets->isEmpty()) {
            throw new \Exception("Allocation rule {$rule->code} has no targets");
        }

        // Check if all target cost centers are active
        foreach ($targets as $target) {
            if (!$target->targetCostCenter->is_active) {
                throw new \Exception("Target cost center {$target->targetCostCenter->code} is not active");
            }
        }

        // Validate based on allocation base
        if ($rule->allocation_base === 'percentage') {
            $totalPercentage = $targets->sum('allocation_percentage');
            if (abs($totalPercentage - 100.00) > 0.01) {
                throw new \Exception("Total allocation percentage must equal 100%, current: {$totalPercentage}%");
            }
        }

        if ($rule->allocation_base === 'formula') {
            if (empty($rule->allocation_formula)) {
                throw new \Exception("Allocation formula is required for formula-based allocation");
            }
            
            // Basic formula validation
            $this->validateFormula($rule->allocation_formula);
        }

        return true;
    }

    /**
     * Calculate allocation amount for each target
     * 
     * @param AllocationRule $rule
     * @param float $sourceCost
     * @return array ['target_cost_center_id' => ['amount' => float, 'calculation_detail' => array]]
     */
    public function calculateAllocationAmount(AllocationRule $rule, float $sourceCost): array
    {
        $targets = $rule->targets()->with('targetCostCenter')->get();
        $allocations = [];

        switch ($rule->allocation_base) {
            case 'percentage':
                foreach ($targets as $target) {
                    $amount = round($sourceCost * ($target->allocation_percentage / 100), 2);
                    $allocations[$target->target_cost_center_id] = [
                        'amount' => $amount,
                        'calculation_detail' => [
                            'method' => 'percentage',
                            'source_amount' => $sourceCost,
                            'percentage' => $target->allocation_percentage,
                            'formula' => "{$sourceCost} * ({$target->allocation_percentage} / 100)",
                        ],
                    ];
                }
                break;

            case 'formula':
                $totalWeight = $this->evaluateFormula($rule->allocation_formula, $sourceCost, $targets);
                
                foreach ($targets as $target) {
                    $weight = $target->allocation_weight ?? 1;
                    $amount = round($sourceCost * ($weight / $totalWeight), 2);
                    $allocations[$target->target_cost_center_id] = [
                        'amount' => $amount,
                        'calculation_detail' => [
                            'method' => 'formula',
                            'source_amount' => $sourceCost,
                            'formula' => $rule->allocation_formula,
                            'weight' => $weight,
                            'total_weight' => $totalWeight,
                        ],
                    ];
                }
                break;

            case 'direct':
                // Direct allocation - split equally
                $count = $targets->count();
                $amountPerTarget = round($sourceCost / $count, 2);
                
                foreach ($targets as $target) {
                    $allocations[$target->target_cost_center_id] = [
                        'amount' => $amountPerTarget,
                        'calculation_detail' => [
                            'method' => 'direct',
                            'source_amount' => $sourceCost,
                            'target_count' => $count,
                        ],
                    ];
                }
                break;

            default:
                throw new \Exception("Unsupported allocation base: {$rule->allocation_base}");
        }

        // Adjust for rounding differences to ensure zero-sum
        $totalAllocated = array_sum(array_column($allocations, 'amount'));
        $difference = $sourceCost - $totalAllocated;
        
        if (abs($difference) > 0.001) {
            // Add difference to first target
            $firstTargetId = array_key_first($allocations);
            $allocations[$firstTargetId]['amount'] = round($allocations[$firstTargetId]['amount'] + $difference, 2);
            $allocations[$firstTargetId]['calculation_detail']['rounding_adjustment'] = $difference;
        }

        return $allocations;
    }

    /**
     * Execute allocation for a period
     * 
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return string Batch ID
     */
    public function executeAllocation(Carbon $periodStart, Carbon $periodEnd): string
    {
        $batchId = 'ALLOC-' . now()->format('YmdHis') . '-' . Str::random(6);

        DB::beginTransaction();
        try {
            // Get all active and approved allocation rules
            $rules = AllocationRule::activeAndApproved()
                ->effective($periodEnd)
                ->with(['sourceCostCenter', 'targets.targetCostCenter'])
                ->get();

            $journals = [];

            foreach ($rules as $rule) {
                // Validate rule
                $this->validateAllocationRule($rule);

                // Get source cost center total cost for the period
                // For now, we'll use a placeholder - in real implementation, 
                // this would query cost_center_transactions
                $sourceCost = $this->getSourceCostForPeriod(
                    $rule->source_cost_center_id,
                    $periodStart,
                    $periodEnd
                );

                if ($sourceCost <= 0) {
                    continue; // Skip if no cost to allocate
                }

                // Calculate allocations
                $allocations = $this->calculateAllocationAmount($rule, $sourceCost);

                // Create allocation journals
                foreach ($allocations as $targetCostCenterId => $allocation) {
                    $journals[] = [
                        'batch_id' => $batchId,
                        'allocation_rule_id' => $rule->id,
                        'source_cost_center_id' => $rule->source_cost_center_id,
                        'target_cost_center_id' => $targetCostCenterId,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'source_amount' => $sourceCost,
                        'allocated_amount' => $allocation['amount'],
                        'allocation_base_value' => $allocation['calculation_detail']['percentage'] ?? null,
                        'calculation_detail' => json_encode($allocation['calculation_detail']),
                        'status' => 'draft',
                        'created_at' => now(),
                    ];
                }
            }

            // Insert all journals
            if (!empty($journals)) {
                AllocationJournal::insert($journals);
            }

            // Validate zero-sum
            $journalCollection = AllocationJournal::where('batch_id', $batchId)->get();
            if (!$this->validateZeroSum($journalCollection)) {
                throw new \Exception("Zero-sum validation failed for batch {$batchId}");
            }

            DB::commit();

            // Dispatch event after successful allocation
            $totalJournals = $journalCollection->count();
            $totalAmount = $journalCollection->sum('allocated_amount');
            $summary = [
                'rules_processed' => $rules->count(),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ];

            event(new AllocationCompleted($batchId, $totalJournals, $totalAmount, $summary));

            return $batchId;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate zero-sum allocation
     * 
     * @param Collection $journals
     * @return bool
     */
    public function validateZeroSum(Collection $journals): bool
    {
        // Group by source cost center
        $grouped = $journals->groupBy('source_cost_center_id');

        foreach ($grouped as $sourceCostCenterId => $sourceJournals) {
            $sourceAmount = $sourceJournals->first()->source_amount;
            $totalAllocated = $sourceJournals->sum('allocated_amount');
            
            $difference = abs($sourceAmount - $totalAllocated);
            
            // Allow small rounding difference (0.01)
            if ($difference > 0.01) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get source cost for period (placeholder)
     * In real implementation, this would query cost_center_transactions
     * 
     * @param int $costCenterId
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return float
     */
    protected function getSourceCostForPeriod(int $costCenterId, Carbon $periodStart, Carbon $periodEnd): float
    {
        // Placeholder - return random cost for testing
        // In production, query cost_center_transactions table
        return 1000000.00;
    }

    /**
     * Validate formula syntax
     * 
     * @param string $formula
     * @return bool
     * @throws \Exception
     */
    protected function validateFormula(string $formula): bool
    {
        // Basic validation - check for dangerous functions
        $dangerousFunctions = ['eval', 'exec', 'system', 'shell_exec', 'passthru'];
        
        foreach ($dangerousFunctions as $func) {
            if (stripos($formula, $func) !== false) {
                throw new \Exception("Formula contains dangerous function: {$func}");
            }
        }

        // Check for basic mathematical operators
        $allowedPattern = '/^[0-9\+\-\*\/\(\)\.\s]+$/';
        if (!preg_match($allowedPattern, $formula)) {
            throw new \Exception("Formula contains invalid characters");
        }

        return true;
    }

    /**
     * Evaluate formula
     * 
     * @param string $formula
     * @param float $sourceAmount
     * @param Collection $targets
     * @return float
     */
    protected function evaluateFormula(string $formula, float $sourceAmount, Collection $targets): float
    {
        // Simple formula evaluation
        // In production, use a proper expression evaluator library
        
        // For now, just sum all weights
        return $targets->sum('allocation_weight') ?: $targets->count();
    }
}
