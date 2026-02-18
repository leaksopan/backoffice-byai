<?php

namespace Modules\MasterDataManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;

class ServiceCatalogApiController extends Controller
{
    public function index(): JsonResponse
    {
        $services = MdmServiceCatalog::with('unit')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $service = MdmServiceCatalog::with('unit')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $service,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function byCategory(string $category): JsonResponse
    {
        $services = MdmServiceCatalog::with('unit')
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function byUnit(int $unitId): JsonResponse
    {
        $services = MdmServiceCatalog::with('unit')
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }
}
