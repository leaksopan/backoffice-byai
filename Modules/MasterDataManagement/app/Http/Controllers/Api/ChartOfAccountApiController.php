<?php

namespace Modules\MasterDataManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\MasterDataManagement\Models\MdmChartOfAccount;

class ChartOfAccountApiController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = MdmChartOfAccount::with('parent')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $account = MdmChartOfAccount::with(['parent', 'children'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $account,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function byCategory(string $category): JsonResponse
    {
        $accounts = MdmChartOfAccount::where('category', $category)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function postable(): JsonResponse
    {
        $accounts = MdmChartOfAccount::where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }
}
