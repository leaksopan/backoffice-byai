<?php

namespace Modules\CostCenterManagement\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\CostCenter;

class CostTransactionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'transaction_date_from' => 'nullable|date',
            'transaction_date_to' => 'nullable|date',
            'transaction_type' => 'nullable|in:direct_cost,allocated_cost,revenue',
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

        $query = CostCenterTransaction::query();

        if ($request->has('cost_center_id')) {
            $query->where('cost_center_id', $request->cost_center_id);
        }

        if ($request->has('transaction_date_from')) {
            $query->where('transaction_date', '>=', $request->transaction_date_from);
        }

        if ($request->has('transaction_date_to')) {
            $query->where('transaction_date', '<=', $request->transaction_date_to);
        }

        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $transactions = $query->with('costCenter')
            ->orderBy('transaction_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions,
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
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:direct_cost,allocated_cost,revenue',
            'category' => 'required|in:personnel,supplies,services,depreciation,overhead,other',
            'amount' => 'required|numeric',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
            'description' => 'nullable|string',
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

        // Validate cost center is active
        $costCenter = CostCenter::find($request->cost_center_id);
        if (!$costCenter->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cost center tidak aktif dan tidak dapat digunakan',
                'error' => [
                    'code' => 'INACTIVE_COST_CENTER',
                    'field' => 'cost_center_id',
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $transaction = CostCenterTransaction::create([
            'cost_center_id' => $request->cost_center_id,
            'transaction_date' => $request->transaction_date,
            'transaction_type' => $request->transaction_type,
            'category' => $request->category,
            'amount' => $request->amount,
            'reference_type' => $request->reference_type,
            'reference_id' => $request->reference_id,
            'description' => $request->description,
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction->load('costCenter'),
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ], 201);
    }

    public function summary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cost_center_id' => 'required|exists:cost_centers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
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

        $transactions = CostCenterTransaction::where('cost_center_id', $request->cost_center_id)
            ->whereBetween('transaction_date', [$request->period_start, $request->period_end])
            ->get();

        $summary = [
            'cost_center_id' => $request->cost_center_id,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'total_direct_cost' => $transactions->where('transaction_type', 'direct_cost')->sum('amount'),
            'total_allocated_cost' => $transactions->where('transaction_type', 'allocated_cost')->sum('amount'),
            'total_revenue' => $transactions->where('transaction_type', 'revenue')->sum('amount'),
            'total_cost' => $transactions->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])->sum('amount'),
            'by_category' => $transactions->groupBy('category')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total_amount' => $items->sum('amount'),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Transaction summary retrieved successfully',
            'data' => $summary,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
