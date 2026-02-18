<?php

namespace Modules\MasterDataManagement\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\MasterDataManagement\Models\MdmFundingSource;

class FundingSourceApiController extends Controller
{
    public function index(): JsonResponse
    {
        $sources = MdmFundingSource::where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sources,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $source = MdmFundingSource::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $source,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function activeOn(string $date): JsonResponse
    {
        $checkDate = Carbon::parse($date);

        $sources = MdmFundingSource::where('is_active', true)
            ->where('start_date', '<=', $checkDate)
            ->where(function ($query) use ($checkDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $checkDate);
            })
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sources,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }
}
