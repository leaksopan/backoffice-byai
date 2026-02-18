<?php

namespace Modules\CostCenterManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CostCenter $costCenter;
    protected MdmOrganizationUnit $orgUnit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'access cost-center-management']);
        Permission::create(['name' => 'cost-center-management.view']);

        // Create user with permissions
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['access cost-center-management', 'cost-center-management.view']);

        // Create organization unit
        $this->orgUnit = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Organization Unit',
            'type' => 'installation',
            'is_active' => true,
        ]);

        // Create cost center
        $this->costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $this->orgUnit->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);
    }

    /** @test */
    public function it_displays_reports_index_page()
    {
        $response = $this->actingAs($this->user)
            ->withoutMiddleware()
            ->get(route('ccm.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('costcentermanagement::reports.index');
    }

    /** @test */
    public function it_displays_cost_center_summary_form()
    {
        $response = $this->actingAs($this->user)
            ->withoutMiddleware()
            ->get(route('ccm.reports.cost-center-summary'));

        $response->assertStatus(200);
        $response->assertViewIs('costcentermanagement::reports.cost-center-summary');
    }

    /** @test */
    public function it_generates_cost_center_summary_report()
    {
        // Create budget
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'actual_amount' => 0,
            'variance_amount' => 0,
            'utilization_percentage' => 0,
            'revision_number' => 0,
        ]);

        // Create transaction
        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 8000000,
        ]);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware()
            ->get(route('ccm.reports.cost-center-summary', [
                'period_start' => '2026-02-01',
                'period_end' => '2026-02-28',
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('costcentermanagement::reports.cost-center-summary');
        $response->assertViewHas('report_data');
        $response->assertViewHas('total_budget');
        $response->assertViewHas('total_actual');
    }

    /** @test */
    public function it_generates_budget_vs_actual_report()
    {
        // Create budget
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'supplies',
            'budget_amount' => 5000000,
            'actual_amount' => 0,
            'variance_amount' => 0,
            'utilization_percentage' => 0,
            'revision_number' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware()
            ->get(route('ccm.reports.budget-vs-actual', [
                'fiscal_year' => 2026,
                'period_month' => 2,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('costcentermanagement::reports.budget-vs-actual');
        $response->assertViewHas('report_data');
    }

    /** @test */
    public function it_generates_variance_analysis_report()
    {
        // Create budget and transaction
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'services',
            'budget_amount' => 3000000,
            'actual_amount' => 0,
            'variance_amount' => 0,
            'utilization_percentage' => 0,
            'revision_number' => 0,
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $this->costCenter->id,
            'transaction_date' => Carbon::create(2026, 2, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'services',
            'amount' => 3500000,
        ]);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware()
            ->get(route('ccm.reports.variance-analysis', [
                'period_start' => '2026-02-01',
                'period_end' => '2026-02-28',
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('costcentermanagement::reports.variance-analysis');
        $response->assertViewHas('report_data');
    }

    /** @test */
    public function it_generates_trend_analysis_report()
    {
        // Create budgets for multiple months
        for ($month = 1; $month <= 3; $month++) {
            CostCenterBudget::create([
                'cost_center_id' => $this->costCenter->id,
                'fiscal_year' => 2026,
                'period_month' => $month,
                'category' => 'personnel',
                'budget_amount' => 10000000,
                'actual_amount' => 0,
                'variance_amount' => 0,
                'utilization_percentage' => 0,
                'revision_number' => 0,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->withoutMiddleware()
            ->get(route('ccm.reports.trend-analysis', [
                'cost_center_id' => $this->costCenter->id,
                'months' => 3,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('costcentermanagement::reports.trend-analysis');
        $response->assertViewHas('trends');
        $response->assertViewHas('statistics');
    }

    /** @test */
    public function it_exports_report_to_csv()
    {
        CostCenterBudget::create([
            'cost_center_id' => $this->costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'actual_amount' => 0,
            'variance_amount' => 0,
            'utilization_percentage' => 0,
            'revision_number' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware()
            ->get(route('ccm.reports.cost-center-summary', [
                'period_start' => '2026-02-01',
                'period_end' => '2026-02-28',
                'format' => 'csv',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function it_requires_authentication_for_reports()
    {
        $response = $this->get(route('ccm.reports.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_requires_permission_for_reports()
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)
            ->withoutMiddleware()
            ->get(route('ccm.reports.index'));

        $response->assertStatus(403);
    }
}
