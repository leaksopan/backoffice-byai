<?php

namespace Modules\CostCenterManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Services\BudgetTrackingService;

class BudgetApiController extends Controller
{
    public function __construct(
        private BudgetTrackingService $budgetService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'fiscal_year' => 'nullable|integer',
            'period_month' => 'nullable|integer|min:1|max:12',
            'category' => 'nullable|in:personnel,supplies,services,depreciation,overhead,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $query = CostCenterBudget::query();

        if ($request->has('cost_center_id')) {
            $query->where('cost_center_id', $request->cost_center_id);
        }

        if ($request->has('fiscal_year')) {
            $query->where('fiscal_year', $request->fiscal_year);
        }

        if ($request->has('period_month')) {
            $query->where('period_month', $request->period_month);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $budgets = $query->with('costCenter')
            ->orderBy('fiscal_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Budgets retrieved successfully',
            'data' => $budgets,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cost_center_id' => 'required|exists:cost_centers,id',
            'fiscal_year' => 'required|integer',
            'period_month' => 'required|integer|min:1|max:12',
            'budgets' => 'required|array',
            'budgets.*.category' => 'required|in:personnel,supplies,services,depreciation,overhead,other',
            'budgets.*.amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        try {
            $categoryAmounts = collect($request->budgets)->pluck('amount', 'category')->toArray();

            $budget = $this->budgetService->setBudget(
                $request->cost_center_id,
                $request->fiscal_year,
                $request->period_month,
                $categoryAmounts
            );

            return response()->json([
                'success' => true,
                'message' => 'Budget set successfully',
                'data' => $budget,
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set budget',
                'error' => [
                    'code' => 'BUDGET_SET_FAILED',
                    'message' => $e->getMessage(),
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    public function available(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cost_center_id' => 'required|exists:cost_centers,id',
            'fiscal_year' => 'required|integer',
            'period_month' => 'required|integer|min:1|max:12',
            'category' => 'required|in:personnel,supplies,services,depreciation,overhead,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $available = $this->budgetService->getAvailableBudget(
            $request->cost_center_id,
            $request->fiscal_year,
            $request->period_month,
            $request->category
        );

        return response()->json([
            'success' => true,
            'message' => 'Available budget retrieved successfully',
            'data' => [
                'cost_center_id' => $request->cost_center_id,
                'fiscal_year' => $request->fiscal_year,
                'period_month' => $request->period_month,
                'category' => $request->category,
                'available_budget' => $available,
            ],
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function variance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cost_center_id' => 'required|exists:cost_centers,id',
            'fiscal_year' => 'required|integer',
            'period_month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $variance = $this->budgetService->calculateVariance(
            $request->cost_center_id,
            $request->fiscal_year,
            $request->period_month
        );

        return response()->json([
            'success' => true,
            'message' => 'Budget variance calculated successfully',
            'data' => $variance,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function utilization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cost_center_id' => 'required|exists:cost_centers,id',
            'fiscal_year' => 'required|integer',
            'period_month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $budgets = CostCenterBudget::where('cost_center_id', $request->cost_center_id)
            ->where('fiscal_year', $request->fiscal_year)
            ->where('period_month', $request->period_month)
            ->get();

        $utilization = $budgets->map(function ($budget) {
            return [
                'category' => $budget->category,
                'budget_amount' => $budget->budget_amount,
                'actual_amount' => $budget->actual_amount,
                'variance_amount' => $budget->variance_amount,
                'utilization_percentage' => $budget->utilization_percentage,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Budget utilization retrieved successfully',
            'data' => [
                'cost_center_id' => $request->cost_center_id,
                'fiscal_year' => $request->fiscal_year,
                'period_month' => $request->period_month,
                'utilization' => $utilization,
            ],
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
