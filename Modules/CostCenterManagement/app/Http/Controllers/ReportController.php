<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Carbon\Carbon;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Services\VarianceAnalysisService;
use Modules\CostCenterManagement\Services\BudgetTrackingService;

class ReportController extends Controller
{
    protected VarianceAnalysisService $varianceService;
    protected BudgetTrackingService $budgetService;

    public function __construct(
        VarianceAnalysisService $varianceService,
        BudgetTrackingService $budgetService
    ) {
        $this->varianceService = $varianceService;
        $this->budgetService = $budgetService;
    }

    /**
     * Display report selection page
     */
    public function index(): View
    {
        return view('costcentermanagement::reports.index');
    }

    /**
     * Cost Center Summary Report
     */
    public function costCenterSummary(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'cost_center_type' => 'nullable|in:medical,non_medical,administrative,profit_center',
            'classification' => 'nullable|string',
            'organization_unit_id' => 'nullable|integer',
            'format' => 'nullable|in:html,excel,pdf,csv',
        ]);

        $periodStart = Carbon::parse($validated['period_start']);
        $periodEnd = Carbon::parse($validated['period_end']);
        $format = $validated['format'] ?? 'html';

        // Build query
        $query = CostCenter::with(['organizationUnit', 'manager'])
            ->active();

        // Apply filters
        if (!empty($validated['cost_center_type'])) {
            $query->where('type', $validated['cost_center_type']);
        }

        if (!empty($validated['classification'])) {
            $query->where('classification', $validated['classification']);
        }

        if (!empty($validated['organization_unit_id'])) {
            $query->where('organization_unit_id', $validated['organization_unit_id']);
        }

        // Row-level security
        $user = auth()->user();
        if (!$user->can('cost-center-management.view-all')) {
            $query->where('manager_user_id', $user->id);
        }

        $costCenters = $query->get();

        // Calculate summary data
        $reportData = [];
        $totalBudget = 0;
        $totalActual = 0;

        foreach ($costCenters as $costCenter) {
            // Get budget
            $budget = CostCenterBudget::forCostCenter($costCenter->id)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->currentRevision()
                ->sum('budget_amount');

            // Get actual costs
            $actual = CostCenterTransaction::where('cost_center_id', $costCenter->id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->where('transaction_type', '!=', 'revenue')
                ->sum('amount');

            // Get revenue (for profit centers)
            $revenue = 0;
            if ($costCenter->type === 'profit_center') {
                $revenue = CostCenterTransaction::where('cost_center_id', $costCenter->id)
                    ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                    ->where('transaction_type', 'revenue')
                    ->sum('amount');
            }

            $variance = $actual - $budget;
            $variancePercentage = $budget > 0 ? ($variance / $budget) * 100 : 0;
            $profit = $revenue - $actual;

            $reportData[] = [
                'cost_center' => $costCenter,
                'budget' => $budget,
                'actual' => $actual,
                'revenue' => $revenue,
                'variance' => $variance,
                'variance_percentage' => $variancePercentage,
                'variance_classification' => $this->varianceService->classifyVariance($variance, $budget),
                'profit' => $profit,
            ];

            $totalBudget += $budget;
            $totalActual += $actual;
        }

        $data = [
            'report_title' => 'Cost Center Summary Report',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'generated_at' => Carbon::now(),
            'generated_by' => $user->name,
            'filters' => $validated,
            'report_data' => $reportData,
            'total_budget' => $totalBudget,
            'total_actual' => $totalActual,
            'total_variance' => $totalActual - $totalBudget,
        ];

        return $this->renderReport('costcentermanagement::reports.cost-center-summary', $data, $format);
    }

    /**
     * Cost Allocation Detail Report
     */
    public function costAllocationDetail(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'batch_id' => 'nullable|string',
            'source_cost_center_id' => 'nullable|integer',
            'target_cost_center_id' => 'nullable|integer',
            'status' => 'nullable|in:draft,posted,reversed',
            'format' => 'nullable|in:html,excel,pdf,csv',
        ]);

        $periodStart = Carbon::parse($validated['period_start']);
        $periodEnd = Carbon::parse($validated['period_end']);
        $format = $validated['format'] ?? 'html';

        // Build query
        $query = AllocationJournal::with([
            'allocationRule',
            'sourceCostCenter',
            'targetCostCenter'
        ])->whereBetween('period_start', [$periodStart, $periodEnd]);

        // Apply filters
        if (!empty($validated['batch_id'])) {
            $query->where('batch_id', $validated['batch_id']);
        }

        if (!empty($validated['source_cost_center_id'])) {
            $query->where('source_cost_center_id', $validated['source_cost_center_id']);
        }

        if (!empty($validated['target_cost_center_id'])) {
            $query->where('target_cost_center_id', $validated['target_cost_center_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $allocations = $query->orderBy('batch_id')->orderBy('created_at')->get();

        // Group by batch
        $batchGroups = $allocations->groupBy('batch_id');

        $reportData = [];
        $totalSourceAmount = 0;
        $totalAllocatedAmount = 0;

        foreach ($batchGroups as $batchId => $batchAllocations) {
            $batchSourceAmount = $batchAllocations->sum('source_amount');
            $batchAllocatedAmount = $batchAllocations->sum('allocated_amount');

            $reportData[] = [
                'batch_id' => $batchId,
                'allocations' => $batchAllocations,
                'batch_source_amount' => $batchSourceAmount,
                'batch_allocated_amount' => $batchAllocatedAmount,
                'batch_difference' => $batchAllocatedAmount - $batchSourceAmount,
            ];

            $totalSourceAmount += $batchSourceAmount;
            $totalAllocatedAmount += $batchAllocatedAmount;
        }

        $data = [
            'report_title' => 'Cost Allocation Detail Report',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'generated_at' => Carbon::now(),
            'generated_by' => auth()->user()->name,
            'filters' => $validated,
            'report_data' => $reportData,
            'total_source_amount' => $totalSourceAmount,
            'total_allocated_amount' => $totalAllocatedAmount,
            'total_difference' => $totalAllocatedAmount - $totalSourceAmount,
        ];

        return $this->renderReport('costcentermanagement::reports.cost-allocation-detail', $data, $format);
    }

    /**
     * Budget vs Actual Report
     */
    public function budgetVsActual(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer',
            'period_month' => 'nullable|integer|min:1|max:12',
            'cost_center_id' => 'nullable|integer',
            'cost_center_type' => 'nullable|in:medical,non_medical,administrative,profit_center',
            'category' => 'nullable|in:personnel,supplies,services,depreciation,overhead,other',
            'format' => 'nullable|in:html,excel,pdf,csv',
        ]);

        $fiscalYear = $validated['fiscal_year'];
        $periodMonth = $validated['period_month'] ?? null;
        $format = $validated['format'] ?? 'html';

        // Build cost center query
        $costCenterQuery = CostCenter::with(['organizationUnit', 'manager'])->active();

        if (!empty($validated['cost_center_id'])) {
            $costCenterQuery->where('id', $validated['cost_center_id']);
        }

        if (!empty($validated['cost_center_type'])) {
            $costCenterQuery->where('type', $validated['cost_center_type']);
        }

        // Row-level security
        $user = auth()->user();
        if (!$user->can('cost-center-management.view-all')) {
            $costCenterQuery->where('manager_user_id', $user->id);
        }

        $costCenters = $costCenterQuery->get();

        // Calculate report data
        $reportData = [];
        $grandTotalBudget = 0;
        $grandTotalActual = 0;

        foreach ($costCenters as $costCenter) {
            $costCenterData = [
                'cost_center' => $costCenter,
                'categories' => [],
                'total_budget' => 0,
                'total_actual' => 0,
                'total_variance' => 0,
            ];

            $categories = ['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other'];

            foreach ($categories as $category) {
                // Skip if category filter is set and doesn't match
                if (!empty($validated['category']) && $category !== $validated['category']) {
                    continue;
                }

                // Get budget
                $budgetQuery = CostCenterBudget::forCostCenter($costCenter->id)
                    ->where('fiscal_year', $fiscalYear)
                    ->where('category', $category)
                    ->currentRevision();

                if ($periodMonth) {
                    $budgetQuery->where('period_month', $periodMonth);
                }

                $budget = $budgetQuery->sum('budget_amount');

                // Get actual
                $actualQuery = CostCenterTransaction::where('cost_center_id', $costCenter->id)
                    ->where('category', $category)
                    ->where('transaction_type', '!=', 'revenue');

                if ($periodMonth) {
                    $periodStart = Carbon::create($fiscalYear, $periodMonth, 1)->startOfMonth();
                    $periodEnd = Carbon::create($fiscalYear, $periodMonth, 1)->endOfMonth();
                    $actualQuery->whereBetween('transaction_date', [$periodStart, $periodEnd]);
                } else {
                    $periodStart = Carbon::create($fiscalYear, 1, 1)->startOfYear();
                    $periodEnd = Carbon::create($fiscalYear, 12, 31)->endOfYear();
                    $actualQuery->whereBetween('transaction_date', [$periodStart, $periodEnd]);
                }

                $actual = $actualQuery->sum('amount');

                $variance = $actual - $budget;
                $variancePercentage = $budget > 0 ? ($variance / $budget) * 100 : 0;

                $costCenterData['categories'][$category] = [
                    'budget' => $budget,
                    'actual' => $actual,
                    'variance' => $variance,
                    'variance_percentage' => $variancePercentage,
                    'variance_classification' => $this->varianceService->classifyVariance($variance, $budget),
                ];

                $costCenterData['total_budget'] += $budget;
                $costCenterData['total_actual'] += $actual;
                $costCenterData['total_variance'] += $variance;
            }

            $reportData[] = $costCenterData;
            $grandTotalBudget += $costCenterData['total_budget'];
            $grandTotalActual += $costCenterData['total_actual'];
        }

        $data = [
            'report_title' => 'Budget vs Actual Report',
            'fiscal_year' => $fiscalYear,
            'period_month' => $periodMonth,
            'period_label' => $periodMonth ? Carbon::create($fiscalYear, $periodMonth, 1)->format('F Y') : "Year $fiscalYear",
            'generated_at' => Carbon::now(),
            'generated_by' => $user->name,
            'filters' => $validated,
            'report_data' => $reportData,
            'grand_total_budget' => $grandTotalBudget,
            'grand_total_actual' => $grandTotalActual,
            'grand_total_variance' => $grandTotalActual - $grandTotalBudget,
        ];

        return $this->renderReport('costcentermanagement::reports.budget-vs-actual', $data, $format);
    }

    /**
     * Variance Analysis Report
     */
    public function varianceAnalysis(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'cost_center_ids' => 'nullable|array',
            'cost_center_ids.*' => 'integer',
            'cost_center_type' => 'nullable|in:medical,non_medical,administrative,profit_center',
            'variance_threshold' => 'nullable|numeric',
            'format' => 'nullable|in:html,excel,pdf,csv',
        ]);

        $periodStart = Carbon::parse($validated['period_start']);
        $periodEnd = Carbon::parse($validated['period_end']);
        $format = $validated['format'] ?? 'html';
        $varianceThreshold = $validated['variance_threshold'] ?? null;

        // Build cost center query
        $costCenterQuery = CostCenter::with(['organizationUnit', 'manager'])->active();

        if (!empty($validated['cost_center_ids'])) {
            $costCenterQuery->whereIn('id', $validated['cost_center_ids']);
        }

        if (!empty($validated['cost_center_type'])) {
            $costCenterQuery->where('type', $validated['cost_center_type']);
        }

        // Row-level security
        $user = auth()->user();
        if (!$user->can('cost-center-management.view-all')) {
            $costCenterQuery->where('manager_user_id', $user->id);
        }

        $costCenters = $costCenterQuery->get();

        // Generate variance analysis
        $reportData = [];

        foreach ($costCenters as $costCenter) {
            $variances = $this->varianceService->calculateVariance(
                $costCenter->id,
                $periodStart,
                $periodEnd
            );

            // Calculate total variance
            $totalBudget = 0;
            $totalActual = 0;
            $totalVariance = 0;

            foreach ($variances as $category => $variance) {
                $totalBudget += $variance['budget'];
                $totalActual += $variance['actual'];
                $totalVariance += $variance['variance'];
            }

            $totalVariancePercentage = $totalBudget > 0 ? ($totalVariance / $totalBudget) * 100 : 0;

            // Apply threshold filter
            if ($varianceThreshold !== null && abs($totalVariancePercentage) < $varianceThreshold) {
                continue;
            }

            $reportData[] = [
                'cost_center' => $costCenter,
                'variances' => $variances,
                'total_budget' => $totalBudget,
                'total_actual' => $totalActual,
                'total_variance' => $totalVariance,
                'total_variance_percentage' => $totalVariancePercentage,
                'variance_classification' => $this->varianceService->classifyVariance($totalVariance, $totalBudget),
            ];
        }

        // Sort by variance percentage (descending)
        usort($reportData, function ($a, $b) {
            return abs($b['total_variance_percentage']) <=> abs($a['total_variance_percentage']);
        });

        $data = [
            'report_title' => 'Variance Analysis Report',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'generated_at' => Carbon::now(),
            'generated_by' => $user->name,
            'filters' => $validated,
            'report_data' => $reportData,
        ];

        return $this->renderReport('costcentermanagement::reports.variance-analysis', $data, $format);
    }

    /**
     * Trend Analysis Report
     */
    public function trendAnalysis(Request $request)
    {
        $validated = $request->validate([
            'cost_center_id' => 'required|integer',
            'months' => 'nullable|integer|min:3|max:24',
            'category' => 'nullable|in:personnel,supplies,services,depreciation,overhead,other',
            'format' => 'nullable|in:html,excel,pdf,csv',
        ]);

        $costCenterId = $validated['cost_center_id'];
        $months = $validated['months'] ?? 12;
        $category = $validated['category'] ?? null;
        $format = $validated['format'] ?? 'html';

        $costCenter = CostCenter::with(['organizationUnit', 'manager'])->findOrFail($costCenterId);

        // Check permission
        $this->authorize('view', $costCenter);

        // Get trend analysis
        $trends = $this->varianceService->getTrendAnalysis($costCenterId, $months);

        // Filter by category if specified
        if ($category) {
            foreach ($trends as &$trend) {
                $trend['budget'] = $trend['categories'][$category]['budget'] ?? 0;
                $trend['actual'] = $trend['categories'][$category]['actual'] ?? 0;
                $trend['variance'] = $trend['categories'][$category]['variance'] ?? 0;
                $trend['variance_percentage'] = $trend['categories'][$category]['variance_percentage'] ?? 0;
            }
        }

        // Calculate statistics
        $budgets = array_column($trends, 'budget');
        $actuals = array_column($trends, 'actual');
        $variances = array_column($trends, 'variance');

        $statistics = [
            'avg_budget' => count($budgets) > 0 ? array_sum($budgets) / count($budgets) : 0,
            'avg_actual' => count($actuals) > 0 ? array_sum($actuals) / count($actuals) : 0,
            'avg_variance' => count($variances) > 0 ? array_sum($variances) / count($variances) : 0,
            'max_budget' => count($budgets) > 0 ? max($budgets) : 0,
            'max_actual' => count($actuals) > 0 ? max($actuals) : 0,
            'min_budget' => count($budgets) > 0 ? min($budgets) : 0,
            'min_actual' => count($actuals) > 0 ? min($actuals) : 0,
        ];

        $data = [
            'report_title' => 'Trend Analysis Report',
            'cost_center' => $costCenter,
            'months' => $months,
            'category' => $category,
            'generated_at' => Carbon::now(),
            'generated_by' => auth()->user()->name,
            'filters' => $validated,
            'trends' => $trends,
            'statistics' => $statistics,
        ];

        return $this->renderReport('costcentermanagement::reports.trend-analysis', $data, $format);
    }

    /**
     * Render report in specified format
     */
    protected function renderReport(string $view, array $data, string $format)
    {
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($data);
            case 'pdf':
                return $this->exportToPdf($view, $data);
            case 'csv':
                return $this->exportToCsv($data);
            default:
                return view($view, $data);
        }
    }

    /**
     * Export report to Excel
     */
    protected function exportToExcel(array $data): Response
    {
        $filename = $this->generateFilename($data['report_title'], 'xlsx');

        // Simple CSV format for Excel compatibility
        $csv = $this->generateCsvContent($data);

        return response($csv, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Export report to PDF
     */
    protected function exportToPdf(string $view, array $data): Response
    {
        $filename = $this->generateFilename($data['report_title'], 'pdf');

        // Render HTML view
        $html = view($view, array_merge($data, ['is_pdf' => true]))->render();

        // Simple PDF generation (in production, use a proper PDF library like dompdf or wkhtmltopdf)
        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Export report to CSV
     */
    protected function exportToCsv(array $data): Response
    {
        $filename = $this->generateFilename($data['report_title'], 'csv');
        $csv = $this->generateCsvContent($data);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Generate CSV content from report data
     */
    protected function generateCsvContent(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        // Write header
        fputcsv($output, [$data['report_title']]);
        fputcsv($output, ['Generated at: ' . $data['generated_at']->format('Y-m-d H:i:s')]);
        fputcsv($output, ['Generated by: ' . $data['generated_by']]);
        fputcsv($output, []); // Empty line

        // Write data based on report type
        if (isset($data['report_data'])) {
            $this->writeCsvData($output, $data);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Write CSV data rows
     */
    protected function writeCsvData($output, array $data): void
    {
        if (empty($data['report_data'])) {
            return;
        }

        $firstRow = $data['report_data'][0];

        // Determine report type and write appropriate headers
        if (isset($firstRow['cost_center'])) {
            // Cost Center Summary or Variance Analysis
            fputcsv($output, ['Code', 'Name', 'Type', 'Budget', 'Actual', 'Variance', 'Variance %', 'Classification']);

            foreach ($data['report_data'] as $row) {
                $costCenter = $row['cost_center'];
                fputcsv($output, [
                    $costCenter->code,
                    $costCenter->name,
                    $costCenter->type,
                    $row['budget'] ?? $row['total_budget'],
                    $row['actual'] ?? $row['total_actual'],
                    $row['variance'] ?? $row['total_variance'],
                    number_format($row['variance_percentage'] ?? $row['total_variance_percentage'], 2),
                    $row['variance_classification'],
                ]);
            }
        } elseif (isset($firstRow['batch_id'])) {
            // Cost Allocation Detail
            fputcsv($output, ['Batch ID', 'Source Cost Center', 'Target Cost Center', 'Source Amount', 'Allocated Amount', 'Allocation Base']);

            foreach ($data['report_data'] as $batch) {
                foreach ($batch['allocations'] as $allocation) {
                    fputcsv($output, [
                        $allocation->batch_id,
                        $allocation->sourceCostCenter->code . ' - ' . $allocation->sourceCostCenter->name,
                        $allocation->targetCostCenter->code . ' - ' . $allocation->targetCostCenter->name,
                        $allocation->source_amount,
                        $allocation->allocated_amount,
                        $allocation->allocationRule->allocation_base ?? 'N/A',
                    ]);
                }
            }
        }
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(string $reportTitle, string $extension): string
    {
        $slug = str_replace(' ', '_', strtolower($reportTitle));
        $timestamp = Carbon::now()->format('Ymd_His');
        return "{$slug}_{$timestamp}.{$extension}";
    }
}
