<?php

namespace Modules\CostCenterManagement\Services;

use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Events\BudgetThresholdExceeded;
use Modules\CostCenterManagement\Events\BudgetRevisionApprovalRequested;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetTrackingService
{
    /**
     * Set budget untuk cost center pada periode tertentu
     */
    public function setBudget(
        int $costCenterId,
        int $year,
        int $month,
        array $categoryAmounts
    ): array {
        $budgets = [];

        DB::beginTransaction();
        try {
            foreach ($categoryAmounts as $category => $amount) {
                $budget = CostCenterBudget::create([
                    'cost_center_id' => $costCenterId,
                    'fiscal_year' => $year,
                    'period_month' => $month,
                    'category' => $category,
                    'budget_amount' => $amount,
                    'actual_amount' => 0,
                    'variance_amount' => 0,
                    'utilization_percentage' => 0,
                    'revision_number' => 0,
                    'created_by' => auth()->id(),
                ]);

                $budgets[] = $budget;
            }

            DB::commit();
            return $budgets;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get available budget untuk cost center pada periode dan category tertentu
     */
    public function getAvailableBudget(
        int $costCenterId,
        int $year,
        int $month,
        string $category
    ): float {
        $budget = CostCenterBudget::forCostCenter($costCenterId)
            ->forPeriod($year, $month)
            ->forCategory($category)
            ->currentRevision()
            ->first();

        if (!$budget) {
            return 0;
        }

        return $budget->getRemainingBudget();
    }

    /**
     * Update budget utilization berdasarkan actual transactions
     */
    public function updateBudgetUtilization(
        int $costCenterId,
        int $year,
        int $month
    ): void {
        $budgets = CostCenterBudget::forCostCenter($costCenterId)
            ->forPeriod($year, $month)
            ->currentRevision()
            ->get();

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        foreach ($budgets as $budget) {
            // Calculate actual amount dari transactions
            $actualAmount = CostCenterTransaction::where('cost_center_id', $costCenterId)
                ->where('category', $budget->category)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->sum('amount');

            // Update budget
            $budget->actual_amount = $actualAmount;
            $budget->variance_amount = $actualAmount - $budget->budget_amount;
            $budget->utilization_percentage = $budget->budget_amount > 0
                ? ($actualAmount / $budget->budget_amount) * 100
                : 0;
            $budget->updated_by = auth()->id();
            $budget->save();

            // Check threshold dan trigger event jika perlu
            if ($this->checkBudgetThreshold($costCenterId, $year, $month, $budget->category)) {
                event(new BudgetThresholdExceeded($budget));
            }
        }
    }

    /**
     * Check apakah budget utilization melebihi threshold
     */
    public function checkBudgetThreshold(
        int $costCenterId,
        int $year,
        int $month,
        ?string $category = null
    ): bool {
        $threshold = config('cost-center-management.budget_threshold', 80.0);

        $query = CostCenterBudget::forCostCenter($costCenterId)
            ->forPeriod($year, $month)
            ->currentRevision()
            ->overUtilized($threshold);

        if ($category) {
            $query->forCategory($category);
        }

        return $query->exists();
    }

    /**
     * Calculate variance analysis untuk cost center
     */
    public function calculateVariance(
        int $costCenterId,
        int $year,
        int $month
    ): array {
        $budgets = CostCenterBudget::forCostCenter($costCenterId)
            ->forPeriod($year, $month)
            ->currentRevision()
            ->get();

        $analysis = [
            'total_budget' => 0,
            'total_actual' => 0,
            'total_variance' => 0,
            'utilization_percentage' => 0,
            'categories' => [],
        ];

        foreach ($budgets as $budget) {
            $analysis['total_budget'] += $budget->budget_amount;
            $analysis['total_actual'] += $budget->actual_amount;
            $analysis['total_variance'] += $budget->variance_amount;

            $analysis['categories'][$budget->category] = [
                'budget_amount' => $budget->budget_amount,
                'actual_amount' => $budget->actual_amount,
                'variance_amount' => $budget->variance_amount,
                'variance_percentage' => $budget->budget_amount > 0
                    ? ($budget->variance_amount / $budget->budget_amount) * 100
                    : 0,
                'utilization_percentage' => $budget->utilization_percentage,
                'variance_type' => $budget->getVarianceType(),
                'is_over_budget' => $budget->isOverBudget(),
            ];
        }

        $analysis['utilization_percentage'] = $analysis['total_budget'] > 0
            ? ($analysis['total_actual'] / $analysis['total_budget']) * 100
            : 0;

        $analysis['variance_percentage'] = $analysis['total_budget'] > 0
            ? ($analysis['total_variance'] / $analysis['total_budget']) * 100
            : 0;

        return $analysis;
    }

    /**
     * Revise budget dengan approval workflow
     */
    public function reviseBudget(
        int $budgetId,
        array $newAmounts,
        string $justification
    ): CostCenterBudget {
        $originalBudget = CostCenterBudget::findOrFail($budgetId);

        DB::beginTransaction();
        try {
            // Get current max revision number
            $maxRevision = CostCenterBudget::forCostCenter($originalBudget->cost_center_id)
                ->forPeriod($originalBudget->fiscal_year, $originalBudget->period_month)
                ->forCategory($originalBudget->category)
                ->max('revision_number');

            // Create new revision
            $revisedBudget = CostCenterBudget::create([
                'cost_center_id' => $originalBudget->cost_center_id,
                'fiscal_year' => $originalBudget->fiscal_year,
                'period_month' => $originalBudget->period_month,
                'category' => $originalBudget->category,
                'budget_amount' => $newAmounts['budget_amount'] ?? $originalBudget->budget_amount,
                'actual_amount' => $originalBudget->actual_amount,
                'variance_amount' => 0, // Will be recalculated
                'utilization_percentage' => 0, // Will be recalculated
                'revision_number' => $maxRevision + 1,
                'revision_justification' => $justification,
                'created_by' => auth()->id(),
            ]);

            // Recalculate variance dan utilization
            $revisedBudget->variance_amount = $revisedBudget->actual_amount - $revisedBudget->budget_amount;
            $revisedBudget->utilization_percentage = $revisedBudget->budget_amount > 0
                ? ($revisedBudget->actual_amount / $revisedBudget->budget_amount) * 100
                : 0;
            $revisedBudget->save();

            DB::commit();

            // Dispatch event untuk approval request
            event(new BudgetRevisionApprovalRequested($revisedBudget, auth()->id(), $justification));

            return $revisedBudget;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get budget summary untuk cost center
     */
    public function getBudgetSummary(
        int $costCenterId,
        int $year,
        ?int $month = null
    ): array {
        $query = CostCenterBudget::forCostCenter($costCenterId)
            ->where('fiscal_year', $year)
            ->currentRevision();

        if ($month) {
            $query->forPeriod($year, $month);
        }

        $budgets = $query->get();

        $summary = [
            'total_budget' => $budgets->sum('budget_amount'),
            'total_actual' => $budgets->sum('actual_amount'),
            'total_variance' => $budgets->sum('variance_amount'),
            'average_utilization' => $budgets->avg('utilization_percentage'),
            'over_budget_count' => $budgets->filter(fn($b) => $b->isOverBudget())->count(),
            'over_threshold_count' => $budgets->filter(fn($b) => $b->isOverThreshold())->count(),
        ];

        return $summary;
    }
}
