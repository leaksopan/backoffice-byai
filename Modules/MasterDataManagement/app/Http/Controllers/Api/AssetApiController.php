<?php

namespace Modules\MasterDataManagement\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\MasterDataManagement\Models\MdmAsset;
use Modules\MasterDataManagement\Services\AssetDepreciationService;

class AssetApiController extends Controller
{
    public function __construct(
        private AssetDepreciationService $depreciationService
    ) {}

    public function index(): JsonResponse
    {
        $assets = MdmAsset::with('currentLocation')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assets,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $asset = MdmAsset::with(['currentLocation', 'movements'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $asset,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function byLocation(int $unitId): JsonResponse
    {
        $assets = MdmAsset::with('currentLocation')
            ->where('current_location_id', $unitId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assets,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function depreciationSchedule(int $id): JsonResponse
    {
        $asset = MdmAsset::findOrFail($id);

        if (!$asset->useful_life_years || !$asset->depreciation_method) {
            return response()->json([
                'success' => false,
                'message' => 'Aset tidak memiliki konfigurasi depresiasi',
                'data' => null,
                'meta' => [
                    'request_id' => request()->id(),
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 400);
        }

        $schedule = [];
        $currentDate = Carbon::parse($asset->acquisition_date);
        $endDate = $currentDate->copy()->addYears($asset->useful_life_years);
        $asOfDate = Carbon::now();

        while ($currentDate->lte($endDate) && $currentDate->lte($asOfDate)) {
            $monthlyDepreciation = $this->depreciationService->calculateMonthlyDepreciation($asset);
            $accumulatedDepreciation = $this->depreciationService->calculateAccumulatedDepreciation($asset, $currentDate);
            $bookValue = $this->depreciationService->getBookValue($asset, $currentDate);

            $schedule[] = [
                'date' => $currentDate->format('Y-m-d'),
                'monthly_depreciation' => $monthlyDepreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'book_value' => $bookValue,
            ];

            $currentDate->addMonth();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'asset' => $asset,
                'schedule' => $schedule,
            ],
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }
}
