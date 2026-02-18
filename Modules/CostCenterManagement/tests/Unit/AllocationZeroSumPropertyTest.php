<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\AllocationRuleTarget;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Services\CostAllocationService;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Carbon\Carbon;

/**
 * Property 9: Zero-Sum Allocation Validation
 * 
 * For any allocation batch execution, the sum of all allocated_amount values 
 * in allocation_journals should equal the sum of all source_amount values 
 * (i.e., total costs before allocation = total costs after allocation)
 * 
 * Validates: Requirements 6.5
 * 
 * @test Feature: cost-center-management, Property 9: Zero-Sum Allocation Validation
 */
class AllocationZeroSumPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected CostAllocationService $allocationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed necessary data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        
        // Create test data
        $this->createTestData();
        
        // Initialize service
        $this->allocationService = new CostAllocationService();
    }

    /**
     * Create test data for property tests
     */
    private function createTestData(): void
    {
        // Create organization units and cost centers
        for ($i = 1; $i <= 20; $i++) {
            $orgUnit = MdmOrganizationUnit::create([
                'code' => 'OU-ZEROSUM-' . $i,
                'name' => 'Zero Sum Test Org Unit ' . $i,
                'type' => 'department',
                'is_active' => true,
            ]);
            
            CostCenter::create([
                'code' => 'CC-ZEROSUM-' . $i,
                'name' => 'Zero Sum Test Cost Center ' . $i,
                'type' => rand(0, 1) ? 'administrative' : 'medical',
                'organization_unit_id' => $orgUnit->id,
                'is_active' => true,
                'effective_date' => now(),
            ]);
        }
    }

    /**
     * Property test: Zero-sum validation for percentage-based allocation
     * 
     * @test
     */
    public function test_zero_sum_property_holds_for_percentage_based_allocation()
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random allocation journals for a batch
            $batchId = 'BATCH-TEST-' . uniqid();
            $journals = $this->generateAllocationJournalsForBatch($batchId, rand(3, 10));
            
            // Insert journals
            foreach ($journals as $journal) {
                AllocationJournal::create($journal);
            }
            
            // Get journals collection
            $journalCollection = AllocationJournal::where('batch_id', $batchId)->get();
            
            // Validate zero-sum
            $isZeroSum = $this->allocationService->validateZeroSum($journalCollection);
            
            // Assert zero-sum property holds
            $this->assertTrue(
                $isZeroSum,
                "Iteration {$i}: Zero-sum validation should pass for batch {$batchId}"
            );
            
            // Additional verification: calculate manually
            $grouped = $journalCollection->groupBy('source_cost_center_id');
            
            foreach ($grouped as $sourceCostCenterId => $sourceJournals) {
                $sourceAmount = $sourceJournals->first()->source_amount;
                $totalAllocated = $sourceJournals->sum('allocated_amount');
                
                $difference = abs($sourceAmount - $totalAllocated);
                
                $this->assertLessThanOrEqual(
                    0.01,
                    $difference,
                    "Iteration {$i}: Difference should be <= 0.01 for source cost center {$sourceCostCenterId}, got {$difference}"
                );
            }
            
            // Cleanup
            AllocationJournal::where('batch_id', $batchId)->delete();
        }
    }

    /**
     * Property test: Zero-sum validation should fail for invalid allocations
     * 
     * @test
     */
    public function test_zero_sum_validation_fails_for_invalid_allocations()
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate invalid allocation journals (intentionally break zero-sum)
            $batchId = 'BATCH-INVALID-' . uniqid();
            $journals = $this->generateInvalidAllocationJournals($batchId, rand(3, 8));
            
            // Insert journals
            foreach ($journals as $journal) {
                AllocationJournal::create($journal);
            }
            
            // Get journals collection
            $journalCollection = AllocationJournal::where('batch_id', $batchId)->get();
            
            // Validate zero-sum
            $isZeroSum = $this->allocationService->validateZeroSum($journalCollection);
            
            // Assert zero-sum validation fails
            $this->assertFalse(
                $isZeroSum,
                "Iteration {$i}: Zero-sum validation should fail for invalid batch {$batchId}"
            );
            
            // Cleanup
            AllocationJournal::where('batch_id', $batchId)->delete();
        }
    }

    /**
     * Property test: Calculate allocation amount maintains zero-sum
     * 
     * @test
     */
    public function test_calculate_allocation_amount_maintains_zero_sum()
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create allocation rule with percentage targets
            $allocationRule = $this->createAllocationRuleWithTargets();
            
            // Generate random source cost
            $sourceCost = round(rand(100000, 10000000) / 100, 2);
            
            // Calculate allocations
            $allocations = $this->allocationService->calculateAllocationAmount($allocationRule, $sourceCost);
            
            // Sum allocated amounts
            $totalAllocated = array_sum(array_column($allocations, 'amount'));
            
            // Assert zero-sum (allowing small rounding difference)
            $difference = abs($sourceCost - $totalAllocated);
            
            $this->assertLessThanOrEqual(
                0.01,
                $difference,
                "Iteration {$i}: Difference between source ({$sourceCost}) and allocated ({$totalAllocated}) should be <= 0.01, got {$difference}"
            );
            
            // Cleanup
            $allocationRule->targets()->delete();
            $allocationRule->delete();
        }
    }

    /**
     * Generate valid allocation journals for a batch (maintains zero-sum)
     */
    private function generateAllocationJournalsForBatch(string $batchId, int $journalCount): array
    {
        $journals = [];
        
        // Group journals by source cost center
        $sourcesCount = rand(2, min(5, $journalCount));
        $journalsPerSource = (int) floor($journalCount / $sourcesCount);
        
        $costCenters = CostCenter::active()->inRandomOrder()->limit($journalCount + $sourcesCount)->get();
        
        if ($costCenters->count() < $journalCount + $sourcesCount) {
            $this->fail('Not enough cost centers for test');
        }
        
        $costCenterIndex = 0;
        
        for ($s = 0; $s < $sourcesCount; $s++) {
            $sourceCostCenter = $costCenters[$costCenterIndex++];
            $sourceAmount = round(rand(100000, 5000000) / 100, 2);
            
            // Create allocation rule for this source
            $allocationRule = AllocationRule::create([
                'code' => 'AR-BATCH-' . uniqid(),
                'name' => 'Batch Test Rule',
                'source_cost_center_id' => $sourceCostCenter->id,
                'allocation_base' => 'percentage',
                'is_active' => true,
                'effective_date' => now(),
                'approval_status' => 'approved',
            ]);
            
            $targetsForThisSource = ($s === $sourcesCount - 1) 
                ? ($journalCount - count($journals)) 
                : $journalsPerSource;
            
            $remainingAmount = $sourceAmount;
            
            for ($t = 0; $t < $targetsForThisSource; $t++) {
                $targetCostCenter = $costCenters[$costCenterIndex++];
                
                // Calculate allocated amount
                if ($t === $targetsForThisSource - 1) {
                    // Last target gets remaining amount (ensures zero-sum)
                    $allocatedAmount = round($remainingAmount, 2);
                } else {
                    // Random allocation
                    $maxAllocation = $remainingAmount - ($targetsForThisSource - $t - 1);
                    $allocatedAmount = round(rand(100, $maxAllocation * 100) / 100, 2);
                }
                
                $journals[] = [
                    'batch_id' => $batchId,
                    'allocation_rule_id' => $allocationRule->id,
                    'source_cost_center_id' => $sourceCostCenter->id,
                    'target_cost_center_id' => $targetCostCenter->id,
                    'period_start' => Carbon::now()->startOfMonth(),
                    'period_end' => Carbon::now()->endOfMonth(),
                    'source_amount' => $sourceAmount,
                    'allocated_amount' => $allocatedAmount,
                    'allocation_base_value' => null,
                    'calculation_detail' => json_encode(['method' => 'test']),
                    'status' => 'draft',
                ];
                
                $remainingAmount -= $allocatedAmount;
            }
        }
        
        return $journals;
    }

    /**
     * Generate invalid allocation journals (breaks zero-sum)
     */
    private function generateInvalidAllocationJournals(string $batchId, int $journalCount): array
    {
        $journals = [];
        
        $costCenters = CostCenter::active()->inRandomOrder()->limit($journalCount + 2)->get();
        
        if ($costCenters->count() < $journalCount + 2) {
            $this->fail('Not enough cost centers for test');
        }
        
        $sourceCostCenter = $costCenters[0];
        $sourceAmount = round(rand(100000, 5000000) / 100, 2);
        
        // Create allocation rule
        $allocationRule = AllocationRule::create([
            'code' => 'AR-INVALID-' . uniqid(),
            'name' => 'Invalid Batch Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        for ($i = 0; $i < $journalCount; $i++) {
            $targetCostCenter = $costCenters[$i + 1];
            
            // Intentionally create wrong allocated amount (doesn't sum to source)
            $allocatedAmount = round(rand(1000, 50000) / 100, 2);
            
            $journals[] = [
                'batch_id' => $batchId,
                'allocation_rule_id' => $allocationRule->id,
                'source_cost_center_id' => $sourceCostCenter->id,
                'target_cost_center_id' => $targetCostCenter->id,
                'period_start' => Carbon::now()->startOfMonth(),
                'period_end' => Carbon::now()->endOfMonth(),
                'source_amount' => $sourceAmount,
                'allocated_amount' => $allocatedAmount,
                'allocation_base_value' => null,
                'calculation_detail' => json_encode(['method' => 'test']),
                'status' => 'draft',
            ];
        }
        
        return $journals;
    }

    /**
     * Create allocation rule with valid percentage targets
     */
    private function createAllocationRuleWithTargets(): AllocationRule
    {
        $costCenters = CostCenter::active()->inRandomOrder()->limit(6)->get();
        
        if ($costCenters->count() < 6) {
            $this->fail('Not enough cost centers for test');
        }
        
        $sourceCostCenter = $costCenters[0];
        
        $allocationRule = AllocationRule::create([
            'code' => 'AR-ZEROSUM-' . uniqid(),
            'name' => 'Zero Sum Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        // Create targets with total = 100%
        $targetCount = rand(2, 5);
        $remainingPercentage = 100.00;
        
        for ($i = 0; $i < $targetCount - 1; $i++) {
            $percentage = round(rand(100, ($remainingPercentage - ($targetCount - $i - 1)) * 100) / 100, 2);
            
            AllocationRuleTarget::create([
                'allocation_rule_id' => $allocationRule->id,
                'target_cost_center_id' => $costCenters[$i + 1]->id,
                'allocation_percentage' => $percentage,
            ]);
            
            $remainingPercentage -= $percentage;
        }
        
        // Last target
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[$targetCount]->id,
            'allocation_percentage' => round($remainingPercentage, 2),
        ]);
        
        return $allocationRule->fresh();
    }
}
