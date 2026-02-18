<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\CostCenterManagement\Http\Controllers\ReportController;
use Modules\CostCenterManagement\Services\VarianceAnalysisService;
use Modules\CostCenterManagement\Services\BudgetTrackingService;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CostCenter $costCenter1;
    protected CostCenter $costCenter2;
    protected MdmOrganizationUnit $orgUnit1;
    protected MdmOrganizationUnit $orgUnit2;
    protected ReportController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'access cost-center-management']);
        Permission::create(['name' => 'cost-center-management.view']);
        Permission::create(['name' => 'cost-center-management.view-all']);

        // Create user with permissions
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'access cost-center-management',
            'cost-center-management.view',
            'cost-center-management.view-all'
        ]);

        // Create organization units
        $this->orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Medical Unit 1',
            'type' => 'installation',
            'is_active' => true,
        ]);

        $this->orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'ORG002',
            'name' => 'Medical Unit 2',
            'type' => 'installation',
            'is_active' => true,
        ]);

        // Create cost centers
        $this->costCenter1 = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Rawat Jalan',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $this->orgUnit1->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        $this->costCenter2 = CostCenter::create([
            'code' => 'CC002',
            'name' => 'Rawat Inap',
            'type' => 'medical',
            'classification' => 'Rawat Inap',
            'organization_unit_id' => $this->orgUnit2->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        // Initialize controller
        $varianceService = app(VarianceAnalysisService::class);
        $budgetService = app(BudgetTrackingService::class);
        $this->controller = new ReportController($varianceService, $budgetService);
    }

    /** @test */
    public function it_calculates_accurate_budget_totals_in_cost_center_summary()
    {
        // Create budgets
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'supplies',
            'budget_amount' => 5000000,
            'revision_number' => 0,
        ]);

        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter2->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 8000000,
            'revision_number' => 0,
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        $data = $response->getData();

        // Verify total budget calculation
        $this->assertEquals(23000000, $data['total_budget']);
        $this->assertCount(2, $data['report_data']);
    }

    /** @test */
    public function it_calculates_accurate_actual_costs_in_cost_center_summary()
    {
        // Create transactions
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 10),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 7500000,
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 3000000,
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter2->id,
            'transaction_date' => Carbon::create(2026, 2, 20),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 6000000,
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        $data = $response->getData();

        // Verify total actual calculation
        $this->assertEquals(16500000, $data['total_actual']);
    }

    /** @test */
    public function it_calculates_accurate_variance_in_cost_center_summary()
    {
        // Create budget
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        // Create transaction
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 12000000,
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        $data = $response->getData();

        // Verify variance calculation
        $this->assertEquals(2000000, $data['total_variance']);
        
        // Verify variance classification
        $reportData = $data['report_data'][0];
        $this->assertEquals('unfavorable', $reportData['variance_classification']);
    }

    /** @test */
    public function it_excludes_revenue_from_cost_calculations()
    {
        // Create cost transaction
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 10),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 5000000,
        ]);

        // Create revenue transaction (should be excluded)
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'revenue',
            'category' => 'other',
            'amount' => 10000000,
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        $data = $response->getData();

        // Verify only cost is included, not revenue
        $this->assertEquals(5000000, $data['total_actual']);
    }

    /** @test */
    public function it_filters_transactions_by_date_range_accurately()
    {
        // Transaction inside range
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 5000000,
        ]);

        // Transaction outside range (should be excluded)
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 3, 5),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 3000000,
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        $data = $response->getData();

        // Verify only transaction in range is included
        $this->assertEquals(5000000, $data['total_actual']);
    }

    /** @test */
    public function it_calculates_accurate_allocation_totals_in_allocation_detail_report()
    {
        // Create allocation rule
        $rule = AllocationRule::create([
            'code' => 'AR001',
            'name' => 'Test Allocation',
            'source_cost_center_id' => $this->costCenter1->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => Carbon::now(),
            'approval_status' => 'approved',
        ]);

        // Create allocation journals
        AllocationJournal::create([
            'batch_id' => 'BATCH001',
            'allocation_rule_id' => $rule->id,
            'source_cost_center_id' => $this->costCenter1->id,
            'target_cost_center_id' => $this->costCenter2->id,
            'period_start' => Carbon::create(2026, 2, 1),
            'period_end' => Carbon::create(2026, 2, 28),
            'source_amount' => 10000000,
            'allocated_amount' => 10000000,
            'status' => 'posted',
        ]);

        AllocationJournal::create([
            'batch_id' => 'BATCH001',
            'allocation_rule_id' => $rule->id,
            'source_cost_center_id' => $this->costCenter1->id,
            'target_cost_center_id' => $this->costCenter2->id,
            'period_start' => Carbon::create(2026, 2, 1),
            'period_end' => Carbon::create(2026, 2, 28),
            'source_amount' => 5000000,
            'allocated_amount' => 5000000,
            'status' => 'posted',
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costAllocationDetail($request);

        $data = $response->getData();

        // Verify totals
        $this->assertEquals(15000000, $data['total_source_amount']);
        $this->assertEquals(15000000, $data['total_allocated_amount']);
        $this->assertEquals(0, $data['total_difference']);
    }

    /** @test */
    public function it_groups_allocations_by_batch_correctly()
    {
        // Create allocation rule
        $rule = AllocationRule::create([
            'code' => 'AR001',
            'name' => 'Test Allocation',
            'source_cost_center_id' => $this->costCenter1->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => Carbon::now(),
            'approval_status' => 'approved',
        ]);

        // Create allocations in different batches
        AllocationJournal::create([
            'batch_id' => 'BATCH001',
            'allocation_rule_id' => $rule->id,
            'source_cost_center_id' => $this->costCenter1->id,
            'target_cost_center_id' => $this->costCenter2->id,
            'period_start' => Carbon::create(2026, 2, 1),
            'period_end' => Carbon::create(2026, 2, 28),
            'source_amount' => 10000000,
            'allocated_amount' => 10000000,
            'status' => 'posted',
        ]);

        AllocationJournal::create([
            'batch_id' => 'BATCH002',
            'allocation_rule_id' => $rule->id,
            'source_cost_center_id' => $this->costCenter1->id,
            'target_cost_center_id' => $this->costCenter2->id,
            'period_start' => Carbon::create(2026, 2, 1),
            'period_end' => Carbon::create(2026, 2, 28),
            'source_amount' => 5000000,
            'allocated_amount' => 5000000,
            'status' => 'posted',
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costAllocationDetail($request);

        $data = $response->getData();

        // Verify batch grouping
        $this->assertCount(2, $data['report_data']);
        $this->assertEquals('BATCH001', $data['report_data'][0]['batch_id']);
        $this->assertEquals('BATCH002', $data['report_data'][1]['batch_id']);
    }

    /** @test */
    public function it_calculates_accurate_budget_vs_actual_by_category()
    {
        // Create budgets for different categories
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'supplies',
            'budget_amount' => 5000000,
            'revision_number' => 0,
        ]);

        // Create transactions
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 9000000,
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 20),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 6000000,
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'fiscal_year' => 2026,
            'period_month' => 2,
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->budgetVsActual($request);

        $data = $response->getData();

        // Verify category calculations
        $costCenterData = $data['report_data'][0];
        
        $this->assertEquals(10000000, $costCenterData['categories']['personnel']['budget']);
        $this->assertEquals(9000000, $costCenterData['categories']['personnel']['actual']);
        $this->assertEquals(-1000000, $costCenterData['categories']['personnel']['variance']);
        $this->assertEquals('favorable', $costCenterData['categories']['personnel']['variance_classification']);

        $this->assertEquals(5000000, $costCenterData['categories']['supplies']['budget']);
        $this->assertEquals(6000000, $costCenterData['categories']['supplies']['actual']);
        $this->assertEquals(1000000, $costCenterData['categories']['supplies']['variance']);
        $this->assertEquals('unfavorable', $costCenterData['categories']['supplies']['variance_classification']);
    }

    /** @test */
    public function it_uses_current_revision_for_budget_calculations()
    {
        // Create original budget
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        // Create revised budget
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 12000000,
            'revision_number' => 1,
        ]);

        // Generate report
        $request = new \Illuminate\Http\Request([
            'fiscal_year' => 2026,
            'period_month' => 2,
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->budgetVsActual($request);

        $data = $response->getData();

        // Verify it uses the latest revision
        $costCenterData = $data['report_data'][0];
        $this->assertEquals(12000000, $costCenterData['categories']['personnel']['budget']);
    }

    /** @test */
    public function it_generates_csv_export_with_correct_structure()
    {
        // Create test data
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter1->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 8000000,
        ]);

        // Generate CSV export
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'format' => 'csv',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        
        // Verify CSV content structure
        $content = $response->getContent();
        $this->assertStringContainsString('Cost Center Summary Report', $content);
        $this->assertStringContainsString('Generated at:', $content);
        $this->assertStringContainsString('CC001', $content);
        $this->assertStringContainsString('Rawat Jalan', $content);
    }

    /** @test */
    public function it_generates_excel_export_with_correct_headers()
    {
        // Create test data
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        // Generate Excel export
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'format' => 'excel',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        // Verify response headers
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function it_generates_pdf_export_with_correct_headers()
    {
        // Create test data
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter1->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'revision_number' => 0,
        ]);

        // Generate PDF export
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'format' => 'pdf',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        // Verify response headers
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function it_includes_metadata_in_all_reports()
    {
        // Generate report
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        $data = $response->getData();

        // Verify metadata
        $this->assertArrayHasKey('report_title', $data);
        $this->assertArrayHasKey('generated_at', $data);
        $this->assertArrayHasKey('generated_by', $data);
        $this->assertArrayHasKey('period_start', $data);
        $this->assertArrayHasKey('period_end', $data);
        
        $this->assertEquals($this->user->name, $data['generated_by']);
        $this->assertInstanceOf(Carbon::class, $data['generated_at']);
    }

    /** @test */
    public function it_handles_empty_data_gracefully()
    {
        // Generate report with no data
        $request = new \Illuminate\Http\Request([
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->actingAs($this->user);
        $response = $this->controller->costCenterSummary($request);

        $data = $response->getData();

        // Verify empty data handling
        $this->assertEquals(0, $data['total_budget']);
        $this->assertEquals(0, $data['total_actual']);
        $this->assertEquals(0, $data['total_variance']);
        $this->assertIsArray($data['report_data']);
    }
}
