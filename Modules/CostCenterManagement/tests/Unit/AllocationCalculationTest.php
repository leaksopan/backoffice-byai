<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\AllocationRuleTarget;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Services\CostAllocationService;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

/**
 * Unit tests untuk allocation calculation
 * 
 * Tests percentage-based allocation, formula-based allocation, and edge cases
 * 
 * Validates: Requirements 4.3, 4.4
 */
class AllocationCalculationTest extends TestCase
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
     * Create test data
     */
    private function createTestData(): void
    {
        // Create organization units and cost centers
        for ($i = 1; $i <= 10; $i++) {
            $orgUnit = MdmOrganizationUnit::create([
                'code' => 'OU-CALC-' . $i,
                'name' => 'Calc Test Org Unit ' . $i,
                'type' => 'department',
                'is_active' => true,
            ]);
            
            CostCenter::create([
                'code' => 'CC-CALC-' . $i,
                'name' => 'Calc Test Cost Center ' . $i,
                'type' => 'administrative',
                'organization_unit_id' => $orgUnit->id,
                'is_active' => true,
                'effective_date' => now(),
            ]);
        }
    }

    /**
     * Test percentage-based allocation
     * 
     * @test
     */
    public function test_percentage_based_allocation_calculates_correctly()
    {
        // Create allocation rule with percentage targets
        $costCenters = CostCenter::active()->limit(4)->get();
        
        $sourceCostCenter = $costCenters[0];
        
        $allocationRule = AllocationRule::create([
            'code' => 'AR-PCT-TEST',
            'name' => 'Percentage Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        // Create targets: 40%, 30%, 30%
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[1]->id,
            'allocation_percentage' => 40.00,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[2]->id,
            'allocation_percentage' => 30.00,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[3]->id,
            'allocation_percentage' => 30.00,
        ]);
        
        // Calculate allocation for 10000.00
        $sourceCost = 10000.00;
        $allocations = $this->allocationService->calculateAllocationAmount($allocationRule, $sourceCost);
        
        // Assert allocations
        $this->assertCount(3, $allocations);
        $this->assertEquals(4000.00, $allocations[$costCenters[1]->id]['amount']);
        $this->assertEquals(3000.00, $allocations[$costCenters[2]->id]['amount']);
        $this->assertEquals(3000.00, $allocations[$costCenters[3]->id]['amount']);
        
        // Assert total equals source
        $totalAllocated = array_sum(array_column($allocations, 'amount'));
        $this->assertEquals($sourceCost, $totalAllocated);
    }

    /**
     * Test formula-based allocation
     * 
     * @test
     */
    public function test_formula_based_allocation_uses_weights()
    {
        // Create allocation rule with formula
        $costCenters = CostCenter::active()->limit(4)->get();
        
        $sourceCostCenter = $costCenters[0];
        
        $allocationRule = AllocationRule::create([
            'code' => 'AR-FORMULA-TEST',
            'name' => 'Formula Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'formula',
            'allocation_formula' => 'source_amount * weight / total_weight',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        // Create targets with weights: 2, 3, 5 (total = 10)
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[1]->id,
            'allocation_weight' => 2.00,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[2]->id,
            'allocation_weight' => 3.00,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[3]->id,
            'allocation_weight' => 5.00,
        ]);
        
        // Calculate allocation for 10000.00
        $sourceCost = 10000.00;
        $allocations = $this->allocationService->calculateAllocationAmount($allocationRule, $sourceCost);
        
        // Assert allocations (2/10, 3/10, 5/10)
        $this->assertCount(3, $allocations);
        $this->assertEquals(2000.00, $allocations[$costCenters[1]->id]['amount']);
        $this->assertEquals(3000.00, $allocations[$costCenters[2]->id]['amount']);
        $this->assertEquals(5000.00, $allocations[$costCenters[3]->id]['amount']);
        
        // Assert total equals source
        $totalAllocated = array_sum(array_column($allocations, 'amount'));
        $this->assertEquals($sourceCost, $totalAllocated);
    }

    /**
     * Test direct allocation (equal split)
     * 
     * @test
     */
    public function test_direct_allocation_splits_equally()
    {
        // Create allocation rule with direct base
        $costCenters = CostCenter::active()->limit(4)->get();
        
        $sourceCostCenter = $costCenters[0];
        
        $allocationRule = AllocationRule::create([
            'code' => 'AR-DIRECT-TEST',
            'name' => 'Direct Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'direct',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        // Create 3 targets (no percentages needed for direct)
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[1]->id,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[2]->id,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[3]->id,
        ]);
        
        // Calculate allocation for 9000.00
        $sourceCost = 9000.00;
        $allocations = $this->allocationService->calculateAllocationAmount($allocationRule, $sourceCost);
        
        // Assert equal split (3000 each)
        $this->assertCount(3, $allocations);
        $this->assertEquals(3000.00, $allocations[$costCenters[1]->id]['amount']);
        $this->assertEquals(3000.00, $allocations[$costCenters[2]->id]['amount']);
        $this->assertEquals(3000.00, $allocations[$costCenters[3]->id]['amount']);
        
        // Assert total equals source
        $totalAllocated = array_sum(array_column($allocations, 'amount'));
        $this->assertEquals($sourceCost, $totalAllocated);
    }

    /**
     * Test edge case: zero amount
     * 
     * @test
     */
    public function test_zero_amount_allocation()
    {
        // Create allocation rule
        $costCenters = CostCenter::active()->limit(3)->get();
        
        $sourceCostCenter = $costCenters[0];
        
        $allocationRule = AllocationRule::create([
            'code' => 'AR-ZERO-TEST',
            'name' => 'Zero Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[1]->id,
            'allocation_percentage' => 50.00,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[2]->id,
            'allocation_percentage' => 50.00,
        ]);
        
        // Calculate allocation for 0.00
        $sourceCost = 0.00;
        $allocations = $this->allocationService->calculateAllocationAmount($allocationRule, $sourceCost);
        
        // Assert all allocations are zero
        $this->assertCount(2, $allocations);
        $this->assertEquals(0.00, $allocations[$costCenters[1]->id]['amount']);
        $this->assertEquals(0.00, $allocations[$costCenters[2]->id]['amount']);
        
        // Assert total equals source
        $totalAllocated = array_sum(array_column($allocations, 'amount'));
        $this->assertEquals($sourceCost, $totalAllocated);
    }

    /**
     * Test edge case: rounding with odd amounts
     * 
     * @test
     */
    public function test_rounding_with_odd_amounts()
    {
        // Create allocation rule
        $costCenters = CostCenter::active()->limit(4)->get();
        
        $sourceCostCenter = $costCenters[0];
        
        $allocationRule = AllocationRule::create([
            'code' => 'AR-ROUND-TEST',
            'name' => 'Rounding Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        // Create targets with percentages that will cause rounding: 33.33%, 33.33%, 33.34%
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[1]->id,
            'allocation_percentage' => 33.33,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[2]->id,
            'allocation_percentage' => 33.33,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[3]->id,
            'allocation_percentage' => 33.34,
        ]);
        
        // Calculate allocation for 10000.00
        $sourceCost = 10000.00;
        $allocations = $this->allocationService->calculateAllocationAmount($allocationRule, $sourceCost);
        
        // Assert allocations
        $this->assertCount(3, $allocations);
        
        // Assert total equals source (rounding adjustment should handle this)
        $totalAllocated = array_sum(array_column($allocations, 'amount'));
        $this->assertEquals($sourceCost, $totalAllocated, 'Total allocated should equal source after rounding adjustment');
        
        // Assert each allocation is close to expected (3333.33)
        foreach ($allocations as $allocation) {
            $this->assertGreaterThanOrEqual(3333.00, $allocation['amount']);
            $this->assertLessThanOrEqual(3334.00, $allocation['amount']);
        }
    }

    /**
     * Test calculation detail is stored correctly
     * 
     * @test
     */
    public function test_calculation_detail_is_stored()
    {
        // Create allocation rule
        $costCenters = CostCenter::active()->limit(3)->get();
        
        $sourceCostCenter = $costCenters[0];
        
        $allocationRule = AllocationRule::create([
            'code' => 'AR-DETAIL-TEST',
            'name' => 'Detail Test Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[1]->id,
            'allocation_percentage' => 60.00,
        ]);
        
        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $costCenters[2]->id,
            'allocation_percentage' => 40.00,
        ]);
        
        // Calculate allocation
        $sourceCost = 5000.00;
        $allocations = $this->allocationService->calculateAllocationAmount($allocationRule, $sourceCost);
        
        // Assert calculation detail exists
        foreach ($allocations as $allocation) {
            $this->assertArrayHasKey('calculation_detail', $allocation);
            $this->assertArrayHasKey('method', $allocation['calculation_detail']);
            $this->assertArrayHasKey('source_amount', $allocation['calculation_detail']);
            $this->assertArrayHasKey('percentage', $allocation['calculation_detail']);
            $this->assertEquals('percentage', $allocation['calculation_detail']['method']);
            $this->assertEquals($sourceCost, $allocation['calculation_detail']['source_amount']);
        }
    }
}
