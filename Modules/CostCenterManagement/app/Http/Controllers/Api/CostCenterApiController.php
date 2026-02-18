<?php

namespace Modules\CostCenterManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Services\CostCenterHierarchyService;

class CostCenterApiController extends Controller
{
    public function __construct(
        private CostCenterHierarchyService $hierarchyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = CostCenter::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('organization_unit_id')) {
            $query->where('organization_unit_id', $request->organization_unit_id);
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $costCenters = $query->with(['organizationUnit', 'parent', 'manager'])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Cost centers retrieved successfully',
            'data' => $costCenters,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function show(CostCenter $costCenter): JsonResponse
    {
        $costCenter->load(['organizationUnit', 'parent', 'children', 'manager']);

        return response()->json([
            'success' => true,
            'message' => 'Cost center retrieved successfully',
            'data' => $costCenter,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function descendants(CostCenter $costCenter): JsonResponse
    {
        $descendants = $this->hierarchyService->getDescendants($costCenter->id);

        return response()->json([
            'success' => true,
            'message' => 'Descendants retrieved successfully',
            'data' => $descendants,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function ancestors(CostCenter $costCenter): JsonResponse
    {
        $ancestors = $this->hierarchyService->getAncestors($costCenter->id);

        return response()->json([
            'success' => true,
            'message' => 'Ancestors retrieved successfully',
            'data' => $ancestors,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function tree(Request $request): JsonResponse
    {
        $rootCostCenters = CostCenter::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with('children');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Cost center tree retrieved successfully',
            'data' => $rootCostCenters,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function byOrganizationUnit(int $organizationUnitId): JsonResponse
    {
        $costCenter = CostCenter::where('organization_unit_id', $organizationUnitId)
            ->where('is_active', true)
            ->first();

        if (!$costCenter) {
            return response()->json([
                'success' => false,
                'message' => 'No active cost center found for this organization unit',
                'data' => null,
                'meta' => [
                    'request_id' => request()->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cost center retrieved successfully',
            'data' => $costCenter,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
