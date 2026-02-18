<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Services\VarianceAnalysisService;
use Modules\CostCenterManagement\Services\BudgetTrackingService;

class CostCenterDashboardController extends Controller
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
     * Display dashboard utama dengan overview semua cost centers
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // Filter cost centers berdasarkan permission
        $costCentersQuery = CostCenter::with(['organizationUnit', 'manager'])
            ->active();
        
        // Row-level security: cost center manager hanya lihat cost center-nya
        if (!$user->can('cost-center-management.view-all')) {
            $costCentersQuery->where('manager_user_id', $user->id);
        }
        
        // Apply filters
        if ($request->filled('type')) {
            $costCentersQuery->where('type', $request->type);
        }
        
        if ($request->filled('classification')) {
            $costCentersQuery->where('classification', $request->classification);
        }
        
        $costCenters = $costCentersQuery->get();
        
        // Get current period
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        
        // Calculate summary metrics
        $summaryMetrics = $this->calculateSummaryMetrics($costCenters, $currentYear, $currentMonth);
        
        return view('costcentermanagement::dashboard.index', [
            'costCenters' => $costCenters,
            'summaryMetrics' => $summaryMetrics,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
        ]);
    }

    /**
     * Display detail dashboard untuk specific cost center
     */
    public function show(Request $request, int $costCenterId): View
    {
        $costCenter = CostCenter::with(['organizationUnit', 'manager', 'parent'])
            ->findOrFail($costCenterId);
        
        // Check permission
        $this->authorize('view', $costCenter);
        
        // Get period dari request atau default ke current month
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Get variance analysis
        $variances = $this->varianceService->calculateVariance(
            $costCenterId,
            $periodStart,
            $periodEnd
        );
        
        // Get trend analysis (last 12 months)
        $trends = $this->varianceService->getTrendAnalysis($costCenterId, 12);
        
        // Get budget summary
        $budgetSummary = $this->budgetService->getBudgetSummary($costCenterId, $year, $month);
        
        // Get recent transactions
        $recentTransactions = CostCenterTransaction::where('cost_center_id', $costCenterId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();
        
        return view('costcentermanagement::dashboard.show', [
            'costCenter' => $costCenter,
            'variances' => $variances,
            'trends' => $trends,
            'budgetSummary' => $budgetSummary,
            'recentTransactions' => $recentTransactions,
            'year' => $year,
            'month' => $month,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ]);
    }

    /**
     * Get real-time cost monitoring data untuk specific cost center (AJAX)
     */
    public function realTimeMonitoring(Request $request, int $costCenterId): JsonResponse
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        
        // Check permission
        $this->authorize('view', $costCenter);
        
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Get current costs
        $currentCosts = CostCenterTransaction::where('cost_center_id', $costCenterId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('transaction_type', '!=', 'revenue')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');
        
        // Get budget
        $budgets = CostCenterBudget::forCostCenter($costCenterId)
            ->forPeriod($year, $month)
            ->currentRevision()
            ->get()
            ->pluck('budget_amount', 'category');
        
        // Calculate utilization
        $utilization = [];
        $categories = ['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other'];
        
        foreach ($categories as $category) {
            $actual = $currentCosts->get($category, 0);
            $budget = $budgets->get($category, 0);
            $percentage = $budget > 0 ? ($actual / $budget) * 100 : 0;
            
            $utilization[$category] = [
                'actual' => $actual,
                'budget' => $budget,
                'remaining' => max(0, $budget - $actual),
                'percentage' => round($percentage, 2),
                'status' => $this->getUtilizationStatus($percentage),
            ];
        }
        
        // Calculate totals
        $totalActual = $currentCosts->sum();
        $totalBudget = $budgets->sum();
        $totalPercentage = $totalBudget > 0 ? ($totalActual / $totalBudget) * 100 : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'cost_center' => [
                    'id' => $costCenter->id,
                    'code' => $costCenter->code,
                    'name' => $costCenter->name,
                    'type' => $costCenter->type,
                ],
                'period' => [
                    'year' => $year,
                    'month' => $month,
                    'start' => $periodStart->format('Y-m-d'),
                    'end' => $periodEnd->format('Y-m-d'),
                ],
                'utilization' => $utilization,
                'summary' => [
                    'total_actual' => $totalActual,
                    'total_budget' => $totalBudget,
                    'total_remaining' => max(0, $totalBudget - $totalActual),
                    'total_percentage' => round($totalPercentage, 2),
                    'status' => $this->getUtilizationStatus($totalPercentage),
                ],
                'timestamp' => Carbon::now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get budget vs actual comparison data (AJAX)
     */
    public function budgetVsActual(Request $request, int $costCenterId): JsonResponse
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        
        // Check permission
        $this->authorize('view', $costCenter);
        
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Get variance analysis
        $variances = $this->varianceService->calculateVariance(
            $costCenterId,
            $periodStart,
            $periodEnd
        );
        
        // Format data untuk chart
        $chartData = [
            'labels' => [],
            'budget' => [],
            'actual' => [],
            'variance' => [],
        ];
        
        $categories = ['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other'];
        
        foreach ($categories as $category) {
            if (isset($variances[$category])) {
                $chartData['labels'][] = ucfirst($category);
                $chartData['budget'][] = $variances[$category]['budget'];
                $chartData['actual'][] = $variances[$category]['actual'];
                $chartData['variance'][] = $variances[$category]['variance'];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'cost_center' => [
                    'id' => $costCenter->id,
                    'code' => $costCenter->code,
                    'name' => $costCenter->name,
                ],
                'period' => [
                    'year' => $year,
                    'month' => $month,
                ],
                'variances' => $variances,
                'chart_data' => $chartData,
            ],
        ]);
    }

    /**
     * Get variance analysis chart data (AJAX)
     */
    public function varianceAnalysis(Request $request, int $costCenterId): JsonResponse
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        
        // Check permission
        $this->authorize('view', $costCenter);
        
        $months = $request->input('months', 12);
        
        // Get trend analysis
        $trends = $this->varianceService->getTrendAnalysis($costCenterId, $months);
        
        // Format data untuk chart
        $chartData = [
            'labels' => [],
            'budget' => [],
            'actual' => [],
            'variance' => [],
            'variance_percentage' => [],
        ];
        
        foreach ($trends as $trend) {
            $chartData['labels'][] = $trend['period_label'];
            $chartData['budget'][] = $trend['budget'];
            $chartData['actual'][] = $trend['actual'];
            $chartData['variance'][] = $trend['variance'];
            $chartData['variance_percentage'][] = $trend['variance_percentage'];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'cost_center' => [
                    'id' => $costCenter->id,
                    'code' => $costCenter->code,
                    'name' => $costCenter->name,
                ],
                'trends' => $trends,
                'chart_data' => $chartData,
            ],
        ]);
    }

    /**
     * Get cost distribution by category (AJAX)
     */
    public function costDistribution(Request $request, int $costCenterId): JsonResponse
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        
        // Check permission
        $this->authorize('view', $costCenter);
        
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Get cost distribution
        $distribution = CostCenterTransaction::where('cost_center_id', $costCenterId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('transaction_type', '!=', 'revenue')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();
        
        $totalCost = $distribution->sum('total');
        
        // Format untuk pie chart
        $chartData = [
            'labels' => [],
            'values' => [],
            'percentages' => [],
        ];
        
        foreach ($distribution as $item) {
            $percentage = $totalCost > 0 ? ($item->total / $totalCost) * 100 : 0;
            
            $chartData['labels'][] = ucfirst($item->category);
            $chartData['values'][] = $item->total;
            $chartData['percentages'][] = round($percentage, 2);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'cost_center' => [
                    'id' => $costCenter->id,
                    'code' => $costCenter->code,
                    'name' => $costCenter->name,
                ],
                'period' => [
                    'year' => $year,
                    'month' => $month,
                ],
                'total_cost' => $totalCost,
                'distribution' => $distribution,
                'chart_data' => $chartData,
            ],
        ]);
    }

    /**
     * Calculate summary metrics untuk dashboard overview
     */
    protected function calculateSummaryMetrics($costCenters, int $year, int $month): array
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        $totalBudget = 0;
        $totalActual = 0;
        $overBudgetCount = 0;
        $overThresholdCount = 0;
        
        foreach ($costCenters as $costCenter) {
            // Get budget
            $budget = CostCenterBudget::forCostCenter($costCenter->id)
                ->forPeriod($year, $month)
                ->currentRevision()
                ->sum('budget_amount');
            
            // Get actual
            $actual = CostCenterTransaction::where('cost_center_id', $costCenter->id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->where('transaction_type', '!=', 'revenue')
                ->sum('amount');
            
            $totalBudget += $budget;
            $totalActual += $actual;
            
            if ($actual > $budget) {
                $overBudgetCount++;
            }
            
            $utilization = $budget > 0 ? ($actual / $budget) * 100 : 0;
            if ($utilization > 80) {
                $overThresholdCount++;
            }
        }
        
        $totalVariance = $totalActual - $totalBudget;
        $utilizationPercentage = $totalBudget > 0 ? ($totalActual / $totalBudget) * 100 : 0;
        
        return [
            'total_cost_centers' => $costCenters->count(),
            'total_budget' => $totalBudget,
            'total_actual' => $totalActual,
            'total_variance' => $totalVariance,
            'utilization_percentage' => round($utilizationPercentage, 2),
            'over_budget_count' => $overBudgetCount,
            'over_threshold_count' => $overThresholdCount,
            'variance_classification' => $this->varianceService->classifyVariance($totalVariance, $totalBudget),
        ];
    }

    /**
     * Get utilization status berdasarkan percentage
     */
    protected function getUtilizationStatus(float $percentage): string
    {
        if ($percentage < 50) {
            return 'low';
        } elseif ($percentage < 80) {
            return 'normal';
        } elseif ($percentage < 100) {
            return 'warning';
        } else {
            return 'critical';
        }
    }
}
