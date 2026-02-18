<?php

namespace Modules\MasterDataManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Services\OrganizationHierarchyService;

class OrganizationUnitApiController extends Controller
{
    public function __construct(
        private OrganizationHierarchyService $hierarchyService
    ) {}

    public function index(): JsonResponse
    {
        $units = MdmOrganizationUnit::with('parent')
            ->where('is_active', true)
            ->orderBy('hierarchy_path')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $units,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $unit = MdmOrganizationUnit::with(['parent', 'children'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function descendants(int $id): JsonResponse
    {
        $descendants = $this->hierarchyService->getDescendants($id);

        return response()->json([
            'success' => true,
            'data' => $descendants,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function tree(): JsonResponse
    {
        $units = MdmOrganizationUnit::where('is_active', true)
            ->orderBy('hierarchy_path')
            ->get();

        $tree = $this->buildTree($units);

        return response()->json([
            'success' => true,
            'data' => $tree,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    private function buildTree($units, $parentId = null): array
    {
        $branch = [];

        foreach ($units as $unit) {
            if ($unit->parent_id == $parentId) {
                $children = $this->buildTree($units, $unit->id);
                $node = $unit->toArray();
                if ($children) {
                    $node['children'] = $children;
                }
                $branch[] = $node;
            }
        }

        return $branch;
    }
}
