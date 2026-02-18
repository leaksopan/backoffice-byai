<?php

namespace Modules\CostCenterManagement\tests\Unit;

use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\ServiceLine;
use Modules\CostCenterManagement\Models\ServiceLineMember;
use Modules\CostCenterManagement\Services\VarianceAnalysisService;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

class VarianceAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VarianceAnalysisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VarianceAnalysisService();
    }

    /** @test */
    public function it_calculates_variance_correctly()
    {
        // Arrange
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Organization Unit',
            'type' => 'installation',
            'is_active' => true
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => Carbon::now()
        ]);

        $periodStart = Carbon::create(2026, 1, 1);
        $periodEnd = Carbon::create(2026, 1, 31);

        // Create budget
        CostCenterBudget::create([
            'cost_center_id' => $costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 1,
            'category' => 'personnel',
            'budget_amount' => 10000000,
            'actual_amount' => 0,
            'variance_amount' => 0,
            'utilization_percentage' => 0
        ]);

        CostCenterBudget::create([
            'cost_center_id' => $costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 1,
            'category' => 'supplies',
            'budget_amount' => 5000000,
            'actual_amount' => 0,
            'variance_amount' => 0,
            'utilization_percentage' => 0
        ]);

        // Create actual transactions
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::create(2026, 1, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 9000000,
            'description' => 'Salary payment'
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::create(2026, 1, 20),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 6000000,
            'description' => 'Medical supplies'
        ]);

        // Act
        $variances = $this->service->calculateVariance($costCenter->id, $periodStart, $periodEnd);

        // Assert
        $this->assertEquals(10000000, $variances['personnel']['budget']);
        $this->assertEquals(9000000, $variances['personnel']['actual']);
        $this->assertEquals(-1000000, $variances['personnel']['variance']);
        $this->assertEquals('favorable', $variances['personnel']['classification']);

        $this->assertEquals(5000000, $variances['supplies']['budget']);
        $this->assertEquals(6000000, $variances['supplies']['actual']);
        $this->assertEquals(1000000, $variances['supplies']['variance']);
        $this->assertEquals('unfavorable', $variances['supplies']['classification']);

        $this->assertEquals(15000000, $variances['total']['budget']);
        $this->assertEquals(15000000, $variances['total']['actual']);
        $this->assertEquals(0, $variances['total']['variance']);
        $this->assertEquals('neutral', $variances['total']['classification']);
    }

    /** @test */
    public function it_classifies_variance_as_favorable_when_actual_less_than_budget()
    {
        // Act
        $classification = $this->service->classifyVariance(-1000000, 10000000);

        // Assert
        $this->assertEquals('favorable', $classification);
    }

    /** @test */
    public function it_classifies_variance_as_unfavorable_when_actual_greater_than_budget()
    {
        // Act
        $classification = $this->service->classifyVariance(1000000, 10000000);

        // Assert
        $this->assertEquals('unfavorable', $classification);
    }

    /** @test */
    public function it_classifies_variance_as_neutral_when_within_threshold()
    {
        // Variance 3% dari budget (threshold 5%)
        $classification = $this->service->classifyVariance(300000, 10000000);

        // Assert
        $this->assertEquals('neutral', $classification);
    }

    /** @test */
    public function it_classifies_variance_as_neutral_when_zero()
    {
        // Act
        $classification = $this->service->classifyVariance(0, 10000000);

        // Assert
        $this->assertEquals('neutral', $classification);
    }

    /** @test */
    public function it_generates_trend_analysis_for_multiple_months()
    {
        // Arrange
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Organization Unit',
            'type' => 'installation',
            'is_active' => true
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => Carbon::now()
        ]);

        // Create budget dan transactions untuk 3 bulan
        for ($month = 1; $month <= 3; $month++) {
            CostCenterBudget::create([
                'cost_center_id' => $costCenter->id,
                'fiscal_year' => 2026,
                'period_month' => $month,
                'category' => 'personnel',
                'budget_amount' => 10000000,
                'actual_amount' => 0,
                'variance_amount' => 0,
                'utilization_percentage' => 0
            ]);

            CostCenterTransaction::create([
                'cost_center_id' => $costCenter->id,
                'transaction_date' => Carbon::create(2026, $month, 15),
                'transaction_type' => 'direct_cost',
                'category' => 'personnel',
                'amount' => 9000000 + ($month * 500000), // Increasing trend
                'description' => 'Salary payment'
            ]);
        }

        // Act
        Carbon::setTestNow(Carbon::create(2026, 3, 31));
        $trends = $this->service->getTrendAnalysis($costCenter->id, 3);

        // Assert
        $this->assertCount(3, $trends);
        
        // Month 1
        $this->assertEquals('2026-01', $trends[0]['period']);
        $this->assertEquals(10000000, $trends[0]['budget']);
        $this->assertEquals(9500000, $trends[0]['actual']);
        $this->assertEquals(-500000, $trends[0]['variance']);
        
        // Month 2
        $this->assertEquals('2026-02', $trends[1]['period']);
        $this->assertEquals(10000000, $trends[1]['budget']);
        $this->assertEquals(10000000, $trends[1]['actual']);
        $this->assertEquals(0, $trends[1]['variance']);
        
        // Month 3
        $this->assertEquals('2026-03', $trends[2]['period']);
        $this->assertEquals(10000000, $trends[2]['budget']);
        $this->assertEquals(10500000, $trends[2]['actual']);
        $this->assertEquals(500000, $trends[2]['variance']);
    }

    /** @test */
    public function it_compares_service_lines_correctly()
    {
        // Arrange
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Organization Unit 1',
            'type' => 'installation',
            'is_active' => true
        ]);

        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'ORG002',
            'name' => 'Test Organization Unit 2',
            'type' => 'installation',
            'is_active' => true
        ]);

        $costCenter1 = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Rawat Jalan',
            'type' => 'profit_center',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => Carbon::now()
        ]);

        $costCenter2 = CostCenter::create([
            'code' => 'CC002',
            'name' => 'Rawat Inap',
            'type' => 'profit_center',
            'organization_unit_id' => $orgUnit2->id,
            'is_active' => true,
            'effective_date' => Carbon::now()
        ]);

        $serviceLine1 = ServiceLine::create([
            'code' => 'SL001',
            'name' => 'Service Line 1',
            'category' => 'rawat_jalan',
            'is_active' => true
        ]);

        $serviceLine2 = ServiceLine::create([
            'code' => 'SL002',
            'name' => 'Service Line 2',
            'category' => 'rawat_inap',
            'is_active' => true
        ]);

        ServiceLineMember::create([
            'service_line_id' => $serviceLine1->id,
            'cost_center_id' => $costCenter1->id,
            'allocation_percentage' => 100
        ]);

        ServiceLineMember::create([
            'service_line_id' => $serviceLine2->id,
            'cost_center_id' => $costCenter2->id,
            'allocation_percentage' => 100
        ]);

        $periodStart = Carbon::create(2026, 1, 1);
        $periodEnd = Carbon::create(2026, 1, 31);

        // Service Line 1: Cost 10M, Revenue 15M, Profit 5M, Margin 33.33%
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter1->id,
            'transaction_date' => Carbon::create(2026, 1, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 10000000,
            'description' => 'Cost'
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $costCenter1->id,
            'transaction_date' => Carbon::create(2026, 1, 15),
            'transaction_type' => 'revenue',
            'category' => 'other',
            'amount' => 15000000,
            'description' => 'Revenue'
        ]);

        // Service Line 2: Cost 20M, Revenue 22M, Profit 2M, Margin 9.09%
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter2->id,
            'transaction_date' => Carbon::create(2026, 1, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 20000000,
            'description' => 'Cost'
        ]);

        CostCenterTransaction::create([
            'cost_center_id' => $costCenter2->id,
            'transaction_date' => Carbon::create(2026, 1, 15),
            'transaction_type' => 'revenue',
            'category' => 'other',
            'amount' => 22000000,
            'description' => 'Revenue'
        ]);

        // Act
        $comparisons = $this->service->compareServiceLines(
            [$serviceLine1->id, $serviceLine2->id],
            $periodStart,
            $periodEnd
        );

        // Assert
        $this->assertCount(2, $comparisons);
        
        // Should be sorted by profit margin descending
        $this->assertEquals($serviceLine1->id, $comparisons[0]['service_line_id']);
        $this->assertEquals(10000000, $comparisons[0]['total_cost']);
        $this->assertEquals(15000000, $comparisons[0]['total_revenue']);
        $this->assertEquals(5000000, $comparisons[0]['profit']);
        $this->assertEquals(33.33, $comparisons[0]['profit_margin']);
        
        $this->assertEquals($serviceLine2->id, $comparisons[1]['service_line_id']);
        $this->assertEquals(20000000, $comparisons[1]['total_cost']);
        $this->assertEquals(22000000, $comparisons[1]['total_revenue']);
        $this->assertEquals(2000000, $comparisons[1]['profit']);
        $this->assertEquals(9.09, $comparisons[1]['profit_margin']);
    }

    /** @test */
    public function it_generates_variance_report_for_multiple_cost_centers()
    {
        // Arrange
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Organization Unit 1',
            'type' => 'installation',
            'is_active' => true
        ]);

        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'ORG002',
            'name' => 'Test Organization Unit 2',
            'type' => 'installation',
            'is_active' => true
        ]);

        $costCenter1 = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Cost Center 1',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => Carbon::now()
        ]);

        $costCenter2 = CostCenter::create([
            'code' => 'CC002',
            'name' => 'Cost Center 2',
            'type' => 'administrative',
            'organization_unit_id' => $orgUnit2->id,
            'is_active' => true,
            'effective_date' => Carbon::now()
        ]);

        $periodStart = Carbon::create(2026, 1, 1);
        $periodEnd = Carbon::create(2026, 1, 31);

        // Create budget dan transactions untuk kedua cost centers
        foreach ([$costCenter1, $costCenter2] as $costCenter) {
            CostCenterBudget::create([
                'cost_center_id' => $costCenter->id,
                'fiscal_year' => 2026,
                'period_month' => 1,
                'category' => 'personnel',
                'budget_amount' => 10000000,
                'actual_amount' => 0,
                'variance_amount' => 0,
                'utilization_percentage' => 0
            ]);

            CostCenterTransaction::create([
                'cost_center_id' => $costCenter->id,
                'transaction_date' => Carbon::create(2026, 1, 15),
                'transaction_type' => 'direct_cost',
                'category' => 'personnel',
                'amount' => 9000000,
                'description' => 'Salary payment'
            ]);
        }

        // Act
        $report = $this->service->generateVarianceReport(
            [$costCenter1->id, $costCenter2->id],
            $periodStart,
            $periodEnd
        );

        // Assert
        $this->assertCount(2, $report);
        
        $this->assertEquals('CC001', $report[0]['cost_center_code']);
        $this->assertEquals('Cost Center 1', $report[0]['cost_center_name']);
        $this->assertEquals(10000000, $report[0]['summary']['total_budget']);
        $this->assertEquals(9000000, $report[0]['summary']['total_actual']);
        $this->assertEquals(-1000000, $report[0]['summary']['total_variance']);
        $this->assertEquals('favorable', $report[0]['summary']['classification']);
        
        $this->assertEquals('CC002', $report[1]['cost_center_code']);
        $this->assertEquals('Cost Center 2', $report[1]['cost_center_name']);
        $this->assertEquals(10000000, $report[1]['summary']['total_budget']);
        $this->assertEquals(9000000, $report[1]['summary']['total_actual']);
        $this->assertEquals(-1000000, $report[1]['summary']['total_variance']);
        $this->assertEquals('favorable', $report[1]['summary']['classification']);
    }

    /** @test */
    public function it_handles_zero_budget_gracefully()
    {
        // Arrange
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Organization Unit',
            'type' => 'installation',
            'is_active' => true
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => Carbon::now()
        ]);

        $periodStart = Carbon::create(2026, 1, 1);
        $periodEnd = Carbon::create(2026, 1, 31);

        // No budget, but has actual transactions
        CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::create(2026, 1, 15),
            'transaction_type' => 'direct_cost',
            'category' => 'personnel',
            'amount' => 5000000,
            'description' => 'Unexpected cost'
        ]);

        // Act
        $variances = $this->service->calculateVariance($costCenter->id, $periodStart, $periodEnd);

        // Assert
        $this->assertEquals(0, $variances['personnel']['budget']);
        $this->assertEquals(5000000, $variances['personnel']['actual']);
        $this->assertEquals(5000000, $variances['personnel']['variance']);
        $this->assertEquals(0, $variances['personnel']['variance_percentage']);
    }
}
