<?php

namespace Modules\MasterDataManagement\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MasterDataManagement\Models\MdmTariff;
use Modules\MasterDataManagement\Services\TariffCalculationService;

class TariffApiController extends Controller
{
    public function __construct(
        private TariffCalculationService $tariffService
    ) {}

    public function applicable(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|integer',
            'class' => 'required|string',
            'date' => 'required|date',
            'payer_type' => 'nullable|string',
        ]);

        $tariff = $this->tariffService->getApplicableTariff(
            $request->integer('service_id'),
            $request->string('class')->toString(),
            Carbon::parse($request->string('date')),
            $request->string('payer_type', 'umum')->toString()
        );

        if (!$tariff) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif tidak ditemukan untuk parameter yang diberikan',
                'data' => null,
                'meta' => [
                    'request_id' => request()->id(),
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tariff->load('breakdowns'),
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function breakdown(int $id): JsonResponse
    {
        $tariff = MdmTariff::with('breakdowns')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'tariff' => $tariff,
                'total' => $this->tariffService->calculateTotalTariff($tariff),
            ],
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }
}
