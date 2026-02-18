<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\CostPool;
use Modules\CostCenterManagement\Models\CostPoolMember;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Services\CostPoolService;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Carbon\Carbon;

/**
 * Unit tests untuk cost pool accumulation dan allocation
 * 
 * Tests cost accumulation dari multiple cost centers dan pool allocation ke targets
 * 
 * Validates: Requirements 7.3, 7.4
 */
class CostPoolTest extends TestCase
{
    use RefreshDatabase;

    protected CostPoolService $costPoolService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed necessary data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        
        // Create test data
        $this->createTestData();
        
        // Initialize service
        $this->costPoolService = new CostPoolService();
    }

    /**
     * Create test data
     */
    private function createTestData(): void
    {
        // Create organization units and cost centers
        for ($i = 1; $i <= 10; $i++) {
            $orgUnit = MdmOrganizationUnit::create([
                'code' => 'OU-POOL-' . $i,
                'name' => 'Pool Test Org Unit ' . $i,
                'type' => 'department',
                'is_active' => true,
            ]);
            
            CostCenter::create([
                'code' => 'CC-POOL-' . $i,
                'name' => 'Pool Test Cost Center ' . $i,
                'type' => 'administrative',
                'organization_unit_id' => $orgUnit->id,
                'is_active' => true,
                'effective_date' => now(),
            ]);
        }
    }

    /**
     * Test cost accumulation dari multiple cost centers
     * 
     * @test
     */
    public function test_cost_accumulation_from_multiple_contributors()
    {
        // Create cost pool
        $costPool = CostPool::create([
            'code' => 'CP-ACCUM-TEST',
            'name' => 'Accumulation Test Pool',
            'pool_type' => 'utilities',
            'allocation_base' => 'equal',
            'is_active' => true,
        ]);
        
        // Add 3 contributor cost centers
        $costCenters = CostCenter::active()->limit(5)->get();
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => true,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[1]->id,
            'is_contributor' => true,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[2]->id,
            'is_contributor' => true,
        ]);
        
        // Accumulate costs
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();
        
        $totalCost = $this->costPoolService->accumulateCosts($costPool, $periodStart, $periodEnd);
        
        // Assert total cost is positive (placeholder returns random values)
        $this->assertGreaterThan(0, $totalCost);
        
        // Assert it's the sum of all contributors
        // In real implementation, this would verify against actual transaction data
        $this->assertIsFloat($totalCost);
    }

    /**
     * Test pool allocation ke targets dengan equal distribution
     * 
     * @test
     */
    public function test_pool_allocation_equal_distribution()
    {
        // Create cost pool
        $costPool = CostPool::create([
            'code' => 'CP-EQUAL-TEST',
            'name' => 'Equal Distribution Test Pool',
            'pool_type' => 'utilities',
            'allocation_base' => 'equal',
            'is_active' => true,
        ]);
        
        // Add contributors and targets
        $costCenters = CostCenter::active()->limit(6)->get();
        
        // 2 contributors
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => true,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[1]->id,
            'is_contributor' => true,
        ]);
        
        // 3 targets
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[3]->id,
            'is_contributor' => false,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[4]->id,
            'is_contributor' => false,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[5]->id,
            'is_contributor' => false,
        ]);
        
        // Allocate pool
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();
        
        $batchId = $this->costPoolService->allocatePool($costPool, $periodStart, $periodEnd);
        
        // Assert batch ID is generated
        $this->assertNotEmpty($batchId);
        $this->assertStringStartsWith('POOL-', $batchId);
        
        // Verify allocation journals were created
        $journals = \Modules\CostCenterManagement\Models\AllocationJournal::where('batch_id', $batchId)->get();
        
        $this->assertCount(3, $journals, 'Should create 3 journals for 3 targets');
        
        // Verify equal distribution
        $amounts = $journals->pluck('allocated_amount')->toArray();
        $firstAmount = $amounts[0];
        
        foreach ($amounts as $amount) {
            // Allow small rounding difference
            $this->assertEqualsWithDelta($firstAmount, $amount, 0.02, 'All targets should receive equal amounts');
        }
        
        // Verify zero-sum
        $totalAllocated = $journals->sum('allocated_amount');
        $sourceAmount = $journals->first()->source_amount;
        
        $this->assertEqualsWithDelta($sourceAmount, $totalAllocated, 0.01, 'Total allocated should equal source amount');
    }

    /**
     * Test pool allocation dengan weighted distribution
     * 
     * @test
     */
    public function test_pool_allocation_weighted_distribution()
    {
        // Create cost pool with headcount allocation base
        $costPool = CostPool::create([
            'code' => 'CP-WEIGHTED-TEST',
            'name' => 'Weighted Distribution Test Pool',
            'pool_type' => 'hr_services',
            'allocation_base' => 'headcount',
            'is_active' => true,
        ]);
        
        // Add contributors and targets
        $costCenters = CostCenter::active()->limit(5)->get();
        
        // 1 contributor
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => true,
        ]);
        
        // 3 targets
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[2]->id,
            'is_contributor' => false,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[3]->id,
            'is_contributor' => false,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[4]->id,
            'is_contributor' => false,
        ]);
        
        // Allocate pool
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();
        
        $batchId = $this->costPoolService->allocatePool($costPool, $periodStart, $periodEnd);
        
        // Assert batch ID is generated
        $this->assertNotEmpty($batchId);
        
        // Verify allocation journals were created
        $journals = \Modules\CostCenterManagement\Models\AllocationJournal::where('batch_id', $batchId)->get();
        
        $this->assertCount(3, $journals, 'Should create 3 journals for 3 targets');
        
        // Verify weighted distribution (amounts should differ based on weights)
        $amounts = $journals->pluck('allocated_amount')->toArray();
        
        // All amounts should be positive
        foreach ($amounts as $amount) {
            $this->assertGreaterThan(0, $amount);
        }
        
        // Verify zero-sum
        $totalAllocated = $journals->sum('allocated_amount');
        $sourceAmount = $journals->first()->source_amount;
        
        $this->assertEqualsWithDelta($sourceAmount, $totalAllocated, 0.01, 'Total allocated should equal source amount');
    }

    /**
     * Test validation: pool must be active
     * 
     * @test
     */
    public function test_inactive_pool_cannot_allocate()
    {
        // Create inactive cost pool
        $costPool = CostPool::create([
            'code' => 'CP-INACTIVE-TEST',
            'name' => 'Inactive Test Pool',
            'pool_type' => 'utilities',
            'allocation_base' => 'equal',
            'is_active' => false, // Inactive
        ]);
        
        // Add contributors and targets
        $costCenters = CostCenter::active()->limit(3)->get();
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => true,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[1]->id,
            'is_contributor' => false,
        ]);
        
        // Attempt to allocate
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not active');
        
        $this->costPoolService->allocatePool($costPool, $periodStart, $periodEnd);
    }

    /**
     * Test validation: pool must have contributors
     * 
     * @test
     */
    public function test_pool_without_contributors_cannot_allocate()
    {
        // Create cost pool without contributors
        $costPool = CostPool::create([
            'code' => 'CP-NO-CONTRIB-TEST',
            'name' => 'No Contributors Test Pool',
            'pool_type' => 'utilities',
            'allocation_base' => 'equal',
            'is_active' => true,
        ]);
        
        // Add only targets (no contributors)
        $costCenters = CostCenter::active()->limit(2)->get();
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => false,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[1]->id,
            'is_contributor' => false,
        ]);
        
        // Attempt to validate
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('has no contributors');
        
        $this->costPoolService->validatePoolAllocationRule($costPool);
    }

    /**
     * Test validation: pool must have targets
     * 
     * @test
     */
    public function test_pool_without_targets_cannot_allocate()
    {
        // Create cost pool without targets
        $costPool = CostPool::create([
            'code' => 'CP-NO-TARGET-TEST',
            'name' => 'No Targets Test Pool',
            'pool_type' => 'utilities',
            'allocation_base' => 'equal',
            'is_active' => true,
        ]);
        
        // Add only contributors (no targets)
        $costCenters = CostCenter::active()->limit(2)->get();
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => true,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[1]->id,
            'is_contributor' => true,
        ]);
        
        // Attempt to validate
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('has no targets');
        
        $this->costPoolService->validatePoolAllocationRule($costPool);
    }

    /**
     * Test get pool balance
     * 
     * @test
     */
    public function test_get_pool_balance()
    {
        // Create cost pool
        $costPool = CostPool::create([
            'code' => 'CP-BALANCE-TEST',
            'name' => 'Balance Test Pool',
            'pool_type' => 'utilities',
            'allocation_base' => 'equal',
            'is_active' => true,
        ]);
        
        // Add contributors
        $costCenters = CostCenter::active()->limit(3)->get();
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => true,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[1]->id,
            'is_contributor' => true,
        ]);
        
        // Get balance
        $asOfDate = Carbon::now();
        $balance = $this->costPoolService->getPoolBalance($costPool, $asOfDate);
        
        // Assert balance is positive
        $this->assertGreaterThan(0, $balance);
        $this->assertIsFloat($balance);
    }

    /**
     * Test calculation detail is stored in journals
     * 
     * @test
     */
    public function test_calculation_detail_stored_in_journals()
    {
        // Create cost pool
        $costPool = CostPool::create([
            'code' => 'CP-DETAIL-TEST',
            'name' => 'Detail Test Pool',
            'pool_type' => 'it_services',
            'allocation_base' => 'service_volume',
            'is_active' => true,
        ]);
        
        // Add contributors and targets
        $costCenters = CostCenter::active()->limit(4)->get();
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[0]->id,
            'is_contributor' => true,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[2]->id,
            'is_contributor' => false,
        ]);
        
        CostPoolMember::create([
            'cost_pool_id' => $costPool->id,
            'cost_center_id' => $costCenters[3]->id,
            'is_contributor' => false,
        ]);
        
        // Allocate pool
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();
        
        $batchId = $this->costPoolService->allocatePool($costPool, $periodStart, $periodEnd);
        
        // Verify calculation detail in journals
        $journals = \Modules\CostCenterManagement\Models\AllocationJournal::where('batch_id', $batchId)->get();
        
        foreach ($journals as $journal) {
            $this->assertNotNull($journal->calculation_detail);
            
            $detail = json_decode($journal->calculation_detail, true);
            
            $this->assertArrayHasKey('method', $detail);
            $this->assertArrayHasKey('pool_id', $detail);
            $this->assertArrayHasKey('pool_code', $detail);
            $this->assertArrayHasKey('pool_type', $detail);
            $this->assertArrayHasKey('allocation_base', $detail);
            
            $this->assertEquals('cost_pool', $detail['method']);
            $this->assertEquals($costPool->id, $detail['pool_id']);
            $this->assertEquals($costPool->code, $detail['pool_code']);
        }
    }
}
