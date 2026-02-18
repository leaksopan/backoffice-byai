<?php

namespace Modules\CostCenterManagement\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Services\BudgetTrackingService;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use App\Models\User;
use Carbon\Carbon;

class BudgetTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected BudgetTrackingService $service;
    protected CostCenter $costCenter;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BudgetTrackingService();

        // Create user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create organization unit
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'ORG-001',
            'name' => 'Test Organization Unit',
            'type' => 'installation',
            'is_active' => true,
        ]);

        // Create cost center
        $this->costCenter = CostCenter::create([
            'code' => 'CC-001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);
    }

    /**
     * Test budget utilization calculation
     * Requirements: 11.3
     */
    public function test_budget_utilization_calculation(): void
    {
        // Set budget
        $budgets = $this->service->setBudget(
            $this->costCenter->id,
            2026,
            2,
            [
                'personnel' => 10000000,
                'supplies' => 5000000,
            ]
        );

        $this->assertCount(2, $budgets);

        // Create transactions
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 8000000, // 80% utilization
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 20),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 6000000, // 120% utilization (over budget)
        ]);

        // Update utilization
        $this->service->updateBudgetUtilization($this->costCenter->id, 2026, 2);

        // Check personnel budget
        $personnelBudget = CostCenterBudget::forCostCenter($this->costCenter->id)
            ->forPeriod(2026, 2)
            ->forCategory('personnel')
            ->first();

        $this->assertEquals(8000000, $personnelBudget->actual_amount);
        $this->assertEquals(-2000000, $personnelBudget->variance_amount); // favorable
        $this->assertEquals(80.00, $personnelBudget->utilization_percentage);

        // Check supplies budget
        $suppliesBudget = CostCenterBudget::forCostCenter($this->costCenter->id)
            ->forPeriod(2026, 2)
            ->forCategory('supplies')
            ->first();

        $this->assertEquals(6000000, $suppliesBudget->actual_amount);
        $this->assertEquals(1000000, $suppliesBudget->variance_amount); // unfavorable
        $this->assertEquals(120.00, $suppliesBudget->utilization_percentage);
        $this->assertTrue($suppliesBudget->isOverBudget());
    }

    /**
     * Test budget threshold checking
     * Requirements: 11.4
     */
    public function test_budget_threshold_checking(): void
    {
        // Set budget
        $this->service->setBudget(
            $this->costCenter->id,
            2026,
            2,
            ['personnel' => 10000000]
        );

        // Create transaction below threshold (70%)
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 7000000,
        ]);

        $this->service->updateBudgetUtilization($this->costCenter->id, 2026, 2);

        // Should not exceed threshold
        $this->assertFalse(
            $this->service->checkBudgetThreshold($this->costCenter->id, 2026, 2, 'personnel')
        );

        // Add more transactions to exceed threshold (85%)
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 20),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 1500000,
        ]);

        $this->service->updateBudgetUtilization($this->costCenter->id, 2026, 2);

        // Should exceed threshold
        $this->assertTrue(
            $this->service->checkBudgetThreshold($this->costCenter->id, 2026, 2, 'personnel')
        );
    }

    /**
     * Test variance calculation
     * Requirements: 11.6
     */
    public function test_variance_calculation(): void
    {
        // Set budgets
        $this->service->setBudget(
            $this->costCenter->id,
            2026,
            2,
            [
                'personnel' => 10000000,
                'supplies' => 5000000,
                'services' => 3000000,
            ]
        );

        // Create transactions
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 9000000, // 90% - favorable
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 20),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 6000000, // 120% - unfavorable
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 25),
            'transaction_type' => 'direct_cost',
            'category' => 'services',
            'amount' => 3000000, // 100% - on target
        ]);

        // Update utilization
        $this->service->updateBudgetUtilization($this->costCenter->id, 2026, 2);

        // Calculate variance
        $analysis = $this->service->calculateVariance($this->costCenter->id, 2026, 2);

        // Check totals
        $this->assertEquals(18000000, $analysis['total_budget']);
        $this->assertEquals(18000000, $analysis['total_actual']);
        $this->assertEquals(0, $analysis['total_variance']);
        $this->assertEquals(100.00, $analysis['utilization_percentage']);

        // Check personnel category
        $this->assertEquals(10000000, $analysis['categories']['personnel']['budget_amount']);
        $this->assertEquals(9000000, $analysis['categories']['personnel']['actual_amount']);
        $this->assertEquals(-1000000, $analysis['categories']['personnel']['variance_amount']);
        $this->assertEquals('favorable', $analysis['categories']['personnel']['variance_type']);
        $this->assertFalse($analysis['categories']['personnel']['is_over_budget']);

        // Check supplies category
        $this->assertEquals(5000000, $analysis['categories']['supplies']['budget_amount']);
        $this->assertEquals(6000000, $analysis['categories']['supplies']['actual_amount']);
        $this->assertEquals(1000000, $analysis['categories']['supplies']['variance_amount']);
        $this->assertEquals('unfavorable', $analysis['categories']['supplies']['variance_type']);
        $this->assertTrue($analysis['categories']['supplies']['is_over_budget']);

        // Check services category
        $this->assertEquals(3000000, $analysis['categories']['services']['budget_amount']);
        $this->assertEquals(3000000, $analysis['categories']['services']['actual_amount']);
        $this->assertEquals(0, $analysis['categories']['services']['variance_amount']);
        $this->assertEquals('on_target', $analysis['categories']['services']['variance_type']);
        $this->assertFalse($analysis['categories']['services']['is_over_budget']);
    }

    /**
     * Test budget revision
     */
    public function test_budget_revision(): void
    {
        // Set initial budget
        $budgets = $this->service->setBudget(
            $this->costCenter->id,
            2026,
            2,
            ['personnel' => 10000000]
        );

        $originalBudget = $budgets[0];
        $this->assertEquals(0, $originalBudget->revision_number);

        // Revise budget
        $revisedBudget = $this->service->reviseBudget(
            $originalBudget->id,
            ['budget_amount' => 12000000],
            'Increased due to additional staff'
        );

        $this->assertEquals(1, $revisedBudget->revision_number);
        $this->assertEquals(12000000, $revisedBudget->budget_amount);
        $this->assertEquals('Increased due to additional staff', $revisedBudget->revision_justification);

        // Original budget should still exist
        $this->assertDatabaseHas('cost_center_budgets', [
            'id' => $originalBudget->id,
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        // Revised budget should exist
        $this->assertDatabaseHas('cost_center_budgets', [
            'id' => $revisedBudget->id,
            'budget_amount' => 12000000,
            'revision_number' => 1,
        ]);
    }

    /**
     * Test available budget calculation
     */
    public function test_available_budget_calculation(): void
    {
        // Set budget
        $this->service->setBudget(
            $this->costCenter->id,
            2026,
            2,
            ['personnel' => 10000000]
        );

        // Initially, available budget = total budget
        $available = $this->service->getAvailableBudget(
            $this->costCenter->id,
            2026,
            2,
            'personnel'
        );
        $this->assertEquals(10000000, $available);

        // Create transaction
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 6000000,
        ]);

        // Update utilization
        $this->service->updateBudgetUtilization($this->costCenter->id, 2026, 2);

        // Available budget should be reduced
        $available = $this->service->getAvailableBudget(
            $this->costCenter->id,
            2026,
            2,
            'personnel'
        );
        $this->assertEquals(4000000, $available);
    }

    /**
     * Test budget summary
     */
    public function test_budget_summary(): void
    {
        // Set budgets
        $this->service->setBudget(
            $this->costCenter->id,
            2026,
            2,
            [
                'personnel' => 10000000,
                'supplies' => 5000000,
            ]
        );

        // Create transactions
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 11000000, // over budget
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 20),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 4500000, // over threshold (90%)
        ]);

        // Update utilization
        $this->service->updateBudgetUtilization($this->costCenter->id, 2026, 2);

        // Get summary
        $summary = $this->service->getBudgetSummary($this->costCenter->id, 2026, 2);

        $this->assertEquals(15000000, $summary['total_budget']);
        $this->assertEquals(15500000, $summary['total_actual']);
        $this->assertEquals(500000, $summary['total_variance']);
        $this->assertEquals(1, $summary['over_budget_count']);
        $this->assertEquals(2, $summary['over_threshold_count']);
    }
}
