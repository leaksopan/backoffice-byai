<?php

namespace Modules\CostCenterManagement\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Modules\CostCenterManagement\Services\CostAllocationService;

class AllocationProcessApiController extends Controller
{
    public function __construct(
        private CostAllocationService $allocationService
    ) {}

    public function execute(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'method' => 'required|in:direct,step_down',
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
            $periodStart = Carbon::parse($request->period_start);
            $periodEnd = Carbon::parse($request->period_end);

            if ($request->method === 'step_down') {
                $this->allocationService->executeStepDownAllocation($periodStart, $periodEnd);
            } else {
                $this->allocationService->executeAllocation($periodStart, $periodEnd);
            }

            return response()->json([
                'success' => true,
                'message' => 'Allocation process executed successfully',
                'data' => [
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'method' => $request->method,
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allocation process failed',
                'error' => [
                    'code' => 'ALLOCATION_FAILED',
                    'message' => $e->getMessage(),
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    public function status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|string',
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

        $journals = AllocationJournal::where('batch_id', $request->batch_id)->get();

        if ($journals->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found',
                'data' => null,
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);
        }

        $summary = [
            'batch_id' => $request->batch_id,
            'total_journals' => $journals->count(),
            'total_source_amount' => $journals->sum('source_amount'),
            'total_allocated_amount' => $journals->sum('allocated_amount'),
            'status_breakdown' => $journals->groupBy('status')->map->count(),
            'posted_count' => $journals->where('status', 'posted')->count(),
            'draft_count' => $journals->where('status', 'draft')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Allocation status retrieved successfully',
            'data' => $summary,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function journals(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'nullable|string',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
            'status' => 'nullable|in:draft,posted,reversed',
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

        $query = AllocationJournal::query();

        if ($request->has('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->has('period_start')) {
            $query->where('period_start', '>=', $request->period_start);
        }

        if ($request->has('period_end')) {
            $query->where('period_end', '<=', $request->period_end);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $journals = $query->with(['sourceCostCenter', 'targetCostCenter', 'allocationRule'])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Allocation journals retrieved successfully',
            'data' => $journals,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function rollback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|string',
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
            $batchId = (int) $request->batch_id;
            $this->allocationService->rollbackAllocation($batchId);

            return response()->json([
                'success' => true,
                'message' => 'Allocation rolled back successfully',
                'data' => [
                    'batch_id' => $request->batch_id,
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rollback failed',
                'error' => [
                    'code' => 'ROLLBACK_FAILED',
                    'message' => $e->getMessage(),
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }
}
