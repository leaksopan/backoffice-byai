<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\MasterDataManagement\Models\MdmTariff;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\Services\TariffCalculationService;

class TariffController extends Controller
{
    public function __construct(
        private TariffCalculationService $tariffService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:master-data-management.view')->only(['index', 'show', 'history']);
        $this->middleware('permission:master-data-management.create')->only(['create', 'store']);
        $this->middleware('permission:master-data-management.edit')->only(['edit', 'update']);
        $this->middleware('permission:master-data-management.delete')->only('destroy');
    }

    public function index(Request $request): View
    {
        $query = MdmTariff::with(['service', 'breakdowns']);

        if ($request->filled('service_id')) {
            $query->forService($request->service_id);
        }

        if ($request->filled('service_class')) {
            $query->forClass($request->service_class);
        }

        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '<=', $request->end_date);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $tariffs = $query->orderBy('start_date', 'desc')->paginate(50);
        $services = MdmServiceCatalog::active()->orderBy('name')->get();

        return view('masterdatamanagement::tariffs.index', compact('tariffs', 'services'));
    }

    public function create(): View
    {
        $services = MdmServiceCatalog::active()->orderBy('name')->get();
        return view('masterdatamanagement::tariffs.create', compact('services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:mdm_service_catalogs,id',
            'service_class' => 'required|in:vip,kelas_1,kelas_2,kelas_3,umum',
            'tariff_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'payer_type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'breakdowns' => 'nullable|array',
            'breakdowns.*.component_type' => 'required|in:jasa_medis,jasa_sarana,bmhp,obat,administrasi',
            'breakdowns.*.amount' => 'required|numeric|min:0',
            'breakdowns.*.percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Validate no period overlap
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        if (!$this->tariffService->validateNoPeriodOverlap(
            $validated['service_id'],
            $validated['service_class'],
            $startDate,
            $endDate,
            $validated['payer_type'] ?? null
        )) {
            return back()->withErrors([
                'start_date' => 'Periode tarif overlap dengan tarif yang sudah ada untuk layanan, kelas, dan payer yang sama.'
            ])->withInput();
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $breakdowns = $validated['breakdowns'] ?? [];
        unset($validated['breakdowns']);

        $tariff = MdmTariff::create($validated);

        if (!empty($breakdowns)) {
            foreach ($breakdowns as $breakdown) {
                $tariff->breakdowns()->create($breakdown);
            }
        }

        return redirect()->route('mdm.tariffs.index')
            ->with('success', 'Tarif berhasil dibuat.');
    }

    public function edit(MdmTariff $tariff): View
    {
        $tariff->load('breakdowns');
        $services = MdmServiceCatalog::active()->orderBy('name')->get();
        return view('masterdatamanagement::tariffs.edit', compact('tariff', 'services'));
    }

    public function update(Request $request, MdmTariff $tariff): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:mdm_service_catalogs,id',
            'service_class' => 'required|in:vip,kelas_1,kelas_2,kelas_3,umum',
            'tariff_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'payer_type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'breakdowns' => 'nullable|array',
            'breakdowns.*.component_type' => 'required|in:jasa_medis,jasa_sarana,bmhp,obat,administrasi',
            'breakdowns.*.amount' => 'required|numeric|min:0',
            'breakdowns.*.percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Validate no period overlap (excluding current tariff)
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        if (!$this->tariffService->validateNoPeriodOverlap(
            $validated['service_id'],
            $validated['service_class'],
            $startDate,
            $endDate,
            $validated['payer_type'] ?? null,
            $tariff->id
        )) {
            return back()->withErrors([
                'start_date' => 'Periode tarif overlap dengan tarif yang sudah ada untuk layanan, kelas, dan payer yang sama.'
            ])->withInput();
        }

        $validated['updated_by'] = auth()->id();

        $breakdowns = $validated['breakdowns'] ?? [];
        unset($validated['breakdowns']);

        $tariff->update($validated);

        // Update breakdowns
        $tariff->breakdowns()->delete();
        if (!empty($breakdowns)) {
            foreach ($breakdowns as $breakdown) {
                $tariff->breakdowns()->create($breakdown);
            }
        }

        return redirect()->route('mdm.tariffs.index')
            ->with('success', 'Tarif berhasil diupdate.');
    }

    public function destroy(MdmTariff $tariff): RedirectResponse
    {
        $tariff->delete();

        return redirect()->route('mdm.tariffs.index')
            ->with('success', 'Tarif berhasil dihapus.');
    }

    public function getApplicableTariff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:mdm_service_catalogs,id',
            'service_class' => 'required|in:vip,kelas_1,kelas_2,kelas_3,umum',
            'payer_type' => 'nullable|string|max:50',
            'date' => 'required|date',
        ]);

        $tariff = $this->tariffService->getApplicableTariff(
            $validated['service_id'],
            $validated['service_class'],
            Carbon::parse($validated['date']),
            $validated['payer_type'] ?? null
        );

        if (!$tariff) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif tidak ditemukan untuk parameter yang diberikan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tariff->id,
                'service_id' => $tariff->service_id,
                'service_name' => $tariff->service->name,
                'service_class' => $tariff->service_class,
                'tariff_amount' => $tariff->tariff_amount,
                'start_date' => $tariff->start_date->format('Y-m-d'),
                'end_date' => $tariff->end_date?->format('Y-m-d'),
                'payer_type' => $tariff->payer_type,
                'breakdowns' => $tariff->breakdowns->map(fn($b) => [
                    'component_type' => $b->component_type,
                    'amount' => $b->amount,
                    'percentage' => $b->percentage,
                ]),
            ],
        ]);
    }

    public function history(int $serviceId): View
    {
        $service = MdmServiceCatalog::findOrFail($serviceId);
        $tariffs = MdmTariff::forService($serviceId)
            ->with('breakdowns')
            ->orderBy('start_date', 'desc')
            ->paginate(50);

        return view('masterdatamanagement::tariffs.history', compact('service', 'tariffs'));
    }
}
