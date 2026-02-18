<?php

namespace Modules\MasterDataManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\MasterDataManagement\Models\MdmHumanResource;

class HumanResourceApiController extends Controller
{
    public function index(): JsonResponse
    {
        $resources = MdmHumanResource::where('is_active', true)
            ->orderBy('nip')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $resources,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $resource = MdmHumanResource::with('assignments.unit')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $resource,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function byUnit(int $unitId): JsonResponse
    {
        $resources = MdmHumanResource::whereHas('assignments', function ($query) use ($unitId) {
            $query->where('unit_id', $unitId)
                ->where('is_active', true);
        })
            ->where('is_active', true)
            ->orderBy('nip')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $resources,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function assignments(int $id): JsonResponse
    {
        $resource = MdmHumanResource::findOrFail($id);
        $assignments = $resource->assignments()
            ->with('unit')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }
}
