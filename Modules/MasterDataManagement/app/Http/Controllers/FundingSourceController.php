<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\MasterDataManagement\Models\MdmFundingSource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class FundingSourceController extends Controller
{
    public function index(): View
    {
        $fundingSources = MdmFundingSource::orderBy('code')->paginate(20);
        
        return view('masterdatamanagement::funding-sources.index', [
            'fundingSources' => $fundingSources
        ]);
    }

    public function create(): View
    {
        return view('masterdatamanagement::funding-sources.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:mdm_funding_sources,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:apbn,apbd_provinsi,apbd_kab_kota,pnbp,hibah,pinjaman,lainnya',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);

        MdmFundingSource::create($validated);

        return redirect()
            ->route('mdm.funding-sources.index')
            ->with('success', 'Sumber dana berhasil dibuat');
    }

    public function edit(MdmFundingSource $fundingSource): View
    {
        return view('masterdatamanagement::funding-sources.edit', [
            'fundingSource' => $fundingSource
        ]);
    }

    public function update(Request $request, MdmFundingSource $fundingSource): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:mdm_funding_sources,code,' . $fundingSource->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:apbn,apbd_provinsi,apbd_kab_kota,pnbp,hibah,pinjaman,lainnya',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', $fundingSource->is_active);

        $fundingSource->update($validated);

        return redirect()
            ->route('mdm.funding-sources.index')
            ->with('success', 'Sumber dana berhasil diperbarui');
    }

    public function destroy(MdmFundingSource $fundingSource): RedirectResponse
    {
        // TODO: Check if funding source is used in transactions
        // For now, just delete
        $fundingSource->delete();

        return redirect()
            ->route('mdm.funding-sources.index')
            ->with('success', 'Sumber dana berhasil dihapus');
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:mdm_funding_sources,id',
            'date' => 'required|date',
        ]);

        $fundingSource = MdmFundingSource::find($validated['source_id']);
        $date = Carbon::parse($validated['date']);

        $isAvailable = $fundingSource->isActiveOn($date);

        return response()->json([
            'success' => true,
            'data' => [
                'is_available' => $isAvailable,
                'funding_source' => [
                    'id' => $fundingSource->id,
                    'code' => $fundingSource->code,
                    'name' => $fundingSource->name,
                    'type' => $fundingSource->type,
                    'start_date' => $fundingSource->start_date->format('Y-m-d'),
                    'end_date' => $fundingSource->end_date?->format('Y-m-d'),
                    'is_active' => $fundingSource->is_active,
                ],
                'checked_date' => $date->format('Y-m-d'),
            ],
        ]);
    }
}
