<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\ServiceLine;
use Modules\CostCenterManagement\Models\ServiceLineMember;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Carbon\Carbon;

/**
 * Unit tests untuk service line costing
 * 
 * Tests cost aggregation dari multiple cost centers dan shared cost center allocation
 * 
 * Validates: Requirements 9.2, 9.3, 9.4
 */
class ServiceLineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed necessary data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        
        // Create test data
        $this->createTestData();
    }

    /**
     * Create test data
     */
    private function createTestData(): void
    {
        // Create organization units and cost centers
        for ($i = 1; $i <= 10; $i++) {
            $orgUnit = MdmOrganizationUnit::create([
                'code' => 'OU-SL-' . $i,
                'name' => 'Service Line Test Org Unit ' . $i,
                'type' => 'department',
                'is_active' => true,
            ]);
            
            CostCenter::create([
                'code' => 'CC-SL-' . $i,
                'name' => 'Service Line Test Cost Center ' . $i,
                'type' => $i <= 5 ? 'medical' : 'non_medical',
                'organization_unit_id' => $orgUnit->id,
                'is_active' => true,
                'effective_date' => now(),
            ]);
        }
    }

    /**
     * Test cost aggregation dari multiple cost centers
     * 
     * @test
     */
    public function test_cost_aggregation_from_multiple_cost_centers()
    {
        // Create service line
        $serviceLine = ServiceLine::create([
            'code' => 'SL-AGGR-TEST',
            'name' => 'Aggregation Test Service Line',
            'category' => 'rawat_jalan',
            'is_active' => true,
        ]);
        
        // Add 3 cost centers with 100% allocation
        $costCenters = CostCenter::active()->limit(3)->get();
        
        foreach ($costCenters as $costCenter) {
            ServiceLineMember::create([
                'service_line_id' => $serviceLine->id,
                'cost_center_id' => $costCenter->id,
                'allocation_percentage' => 100.00,
            ]);
            
            // Create transactions for each cost center
            CostCenterTransaction::create([
                'cost_center_id' => $costCenter->id,
                'transaction_date' => Carbon::now(),
                'transaction_type' => 'direct_cost',
                'category' => 'personnel',
                'amount' => 1000000,
            ]);
            
            CostCenterTransaction::create([
                'cost_center_id' => $costCenter->id,
                'transaction_date' => Carbon::now(),
                'transaction_type' => 'direct_cost',
                'category' => 'supplies',
                'amount' => 500000,
            ]);
        }
        
        // Calculate total costs
        $totalCosts = $this->calculateTotalCosts($serviceLine, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        
        // Assert total costs = 3 cost centers * (1,000,000 + 500,000) = 4,500,000
        $this->assertEquals(4500000, $totalCosts);
    }

    /**
     * Test shared cost center allocation dengan percentage
     * 
     * @test
     */
    public function test_shared_cost_center_allocation()
    {
        // Create two service lines
        $serviceLine1 = ServiceLine::create([
            'code' => 'SL-SHARED-1',
            'name' => 'Shared Test Service Line 1',
            'category' => 'rawat_jalan',
            'is_active' => true,
        ]);
        
        $serviceLine2 = ServiceLine::create([
            'code' => 'SL-SHARED-2',
            'name' => 'Shared Test Service Line 2',
            'category' => 'rawat_inap',
            'is_active' => true,
        ]);
        
        // Create a shared cost center
        $sharedCostCenter = CostCenter::active()->first();
        
        // Add shared cost center to both service lines with different percentages
        ServiceLineMember::create([
            'service_line_id' => $serviceLine1->id,
            'cost_center_id' => $sharedCostCenter->id,
            'allocation_percentage' => 60.00, // 60% to service line 1
        ]);
        
        ServiceLineMember::create([
            'service_line_id' => $serviceLine2->id,
            'cost_center_id' => $sharedCostCenter->id,
            'allocation_percentage' => 40.00, // 40% to service line 2
        ]);
        
        // Create transactions for shared cost center
        CostCenterTransaction::create([
            'cost_center_id' => $sharedCostCenter->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 1000000, // Total cost
        ]);
        
        // Calculate costs for each service line
        $costs1 = $this->calculateTotalCosts($serviceLine1, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $costs2 = $this->calculateTotalCosts($serviceLine2, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        
        // Assert allocation percentages are applied correctly
        $this->assertEquals(600000, $costs1, 'Service Line 1 should get 60% = 600,000');
        $this->assertEquals(400000, $costs2, 'Service Line 2 should get 40% = 400,000');
        
        // Assert total = 100%
        $this->assertEquals(1000000, $costs1 + $costs2, 'Total should equal original amount');
    }

    /**
     * Test cost aggregation by category
     * 
     * @test
     */
    public function test_cost_aggregation_by_category()
    {
        // Create service line
        $serviceLine = ServiceLine::create([
            'code' => 'SL-CATEGORY-TEST',
            'name' => 'Category Test Service Line',
            'category' => 'igd',
            'is_active' => true,
        ]);
        
        // Add cost center
        $costCenter = CostCenter::active()->first();
        
        ServiceLineMember::create([
            'service_line_id' => $serviceLine->id,
            'cost_center_id' => $costCenter->id,
            'allocation_percentage' => 100.00,
        ]);
        
        // Create transactions with different categories
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 2000000,
        ]);
        
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 1500000,
        ]);
        
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'services',
            'amount' => 1000000,
        ]);
        
        // Calculate costs by category
        $costsByCategory = $this->calculateCostsByCategory($serviceLine, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        
        // Assert costs are grouped correctly
        $this->assertEquals(2000000, $costsByCategory['personnel']);
        $this->assertEquals(1500000, $costsByCategory['supplies']);
        $this->assertEquals(1000000, $costsByCategory['services']);
        
        // Assert total
        $total = array_sum($costsByCategory);
        $this->assertEquals(4500000, $total);
    }

    /**
     * Test revenue calculation for profitability
     * 
     * @test
     */
    public function test_revenue_calculation_for_profitability()
    {
        // Create service line
        $serviceLine = ServiceLine::create([
            'code' => 'SL-PROFIT-TEST',
            'name' => 'Profitability Test Service Line',
            'category' => 'operasi',
            'is_active' => true,
        ]);
        
        // Add cost center
        $costCenter = CostCenter::active()->first();
        
        ServiceLineMember::create([
            'service_line_id' => $serviceLine->id,
            'cost_center_id' => $costCenter->id,
            'allocation_percentage' => 100.00,
        ]);
        
        // Create cost transactions
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 3000000,
        ]);
        
        // Create revenue transactions
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'revenue',
            'category' => 'other',
            'amount' => 5000000,
        ]);
        
        // Calculate costs and revenue
        $totalCosts = $this->calculateTotalCosts($serviceLine, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $totalRevenue = $this->calculateTotalRevenue($serviceLine, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        
        // Assert values
        $this->assertEquals(3000000, $totalCosts);
        $this->assertEquals(5000000, $totalRevenue);
        
        // Calculate profit margin
        $profit = $totalRevenue - $totalCosts;
        $profitMargin = ($profit / $totalRevenue) * 100;
        
        $this->assertEquals(2000000, $profit);
        $this->assertEquals(40, $profitMargin);
    }

    /**
     * Test multiple cost centers with mixed allocation percentages
     * 
     * @test
     */
    public function test_multiple_cost_centers_mixed_allocation()
    {
        // Create service line
        $serviceLine = ServiceLine::create([
            'code' => 'SL-MIXED-TEST',
            'name' => 'Mixed Allocation Test Service Line',
            'category' => 'rawat_inap',
            'is_active' => true,
        ]);
        
        // Add 3 cost centers with different allocation percentages
        $costCenters = CostCenter::active()->limit(3)->get();
        
        // Cost Center 1: 100% dedicated
        ServiceLineMember::create([
            'service_line_id' => $serviceLine->id,
            'cost_center_id' => $costCenters[0]->id,
            'allocation_percentage' => 100.00,
        ]);
        
        CostCenterTransaction::create([
            'cost_center_id' => $costCenters[0]->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 1000000,
        ]);
        
        // Cost Center 2: 70% shared
        ServiceLineMember::create([
            'service_line_id' => $serviceLine->id,
            'cost_center_id' => $costCenters[1]->id,
            'allocation_percentage' => 70.00,
        ]);
        
        CostCenterTransaction::create([
            'cost_center_id' => $costCenters[1]->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 2000000,
        ]);
        
        // Cost Center 3: 30% shared
        ServiceLineMember::create([
            'service_line_id' => $serviceLine->id,
            'cost_center_id' => $costCenters[2]->id,
            'allocation_percentage' => 30.00,
        ]);
        
        CostCenterTransaction::create([
            'cost_center_id' => $costCenters[2]->id,
            'transaction_date' => Carbon::now(),
            'transaction_type' => 'direct_cost',
            'category' => 'services',
            'amount' => 3000000,
        ]);
        
        // Calculate total costs
        $totalCosts = $this->calculateTotalCosts($serviceLine, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        
        // Expected: 1,000,000 (100%) + 1,400,000 (70% of 2M) + 900,000 (30% of 3M) = 3,300,000
        $this->assertEquals(3300000, $totalCosts);
    }

    /**
     * Test edge case: service line with no cost centers
     * 
     * @test
     */
    public function test_service_line_with_no_cost_centers()
    {
        // Create service line without members
        $serviceLine = ServiceLine::create([
            'code' => 'SL-EMPTY-TEST',
            'name' => 'Empty Test Service Line',
            'category' => 'penunjang',
            'is_active' => true,
        ]);
        
        // Calculate costs
        $totalCosts = $this->calculateTotalCosts($serviceLine, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        
        // Assert zero costs
        $this->assertEquals(0, $totalCosts);
    }

    /**
     * Test edge case: cost center with no transactions
     * 
     * @test
     */
    public function test_cost_center_with_no_transactions()
    {
        // Create service line
        $serviceLine = ServiceLine::create([
            'code' => 'SL-NO-TRX-TEST',
            'name' => 'No Transactions Test Service Line',
            'category' => 'icu',
            'is_active' => true,
        ]);
        
        // Add cost center (but don't create transactions)
        $costCenter = CostCenter::active()->first();
        
        ServiceLineMember::create([
            'service_line_id' => $serviceLine->id,
            'cost_center_id' => $costCenter->id,
            'allocation_percentage' => 100.00,
        ]);
        
        // Calculate costs
        $totalCosts = $this->calculateTotalCosts($serviceLine, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        
        // Assert zero costs
        $this->assertEquals(0, $totalCosts);
    }

    /**
     * Helper: Calculate total costs for a service line
     */
    private function calculateTotalCosts(ServiceLine $serviceLine, Carbon $periodStart, Carbon $periodEnd): float
    {
        $totalCosts = 0;

        foreach ($serviceLine->members as $member) {
            $costCenterCosts = CostCenterTransaction::where('cost_center_id', $member->cost_center_id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
                ->sum('amount');

            $allocatedCosts = $costCenterCosts * ($member->allocation_percentage / 100);
            $totalCosts += $allocatedCosts;
        }

        return $totalCosts;
    }

    /**
     * Helper: Calculate costs by category for a service line
     */
    private function calculateCostsByCategory(ServiceLine $serviceLine, Carbon $periodStart, Carbon $periodEnd): array
    {
        $costsByCategory = [];

        foreach ($serviceLine->members as $member) {
            $categoryCosts = CostCenterTransaction::where('cost_center_id', $member->cost_center_id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get();

            foreach ($categoryCosts as $categoryCost) {
                $category = $categoryCost->category;
                $allocatedAmount = $categoryCost->total * ($member->allocation_percentage / 100);

                if (!isset($costsByCategory[$category])) {
                    $costsByCategory[$category] = 0;
                }
                $costsByCategory[$category] += $allocatedAmount;
            }
        }

        return $costsByCategory;
    }

    /**
     * Helper: Calculate total revenue for a service line
     */
    private function calculateTotalRevenue(ServiceLine $serviceLine, Carbon $periodStart, Carbon $periodEnd): float
    {
        $totalRevenue = 0;

        foreach ($serviceLine->members as $member) {
            $costCenterRevenue = CostCenterTransaction::where('cost_center_id', $member->cost_center_id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->where('transaction_type', 'revenue')
                ->sum('amount');

            $allocatedRevenue = $costCenterRevenue * ($member->allocation_percentage / 100);
            $totalRevenue += $allocatedRevenue;
        }

        return $totalRevenue;
    }
}
