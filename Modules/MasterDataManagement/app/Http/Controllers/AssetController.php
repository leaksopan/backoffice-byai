<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\MasterDataManagement\Models\MdmAsset;
use Modules\MasterDataManagement\Models\MdmAssetMovement;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Services\AssetDepreciationService;
use Modules\MasterDataManagement\Http\Requests\StoreAssetRequest;
use Modules\MasterDataManagement\Http\Requests\UpdateAssetRequest;
use Modules\MasterDataManagement\Http\Requests\MoveAssetRequest;

class AssetController extends Controller
{
    public function __construct(
        private AssetDepreciationService $depreciationService
    ) {}

    public function index(Request $request): View
    {
        $query = MdmAsset::query()->with('currentLocation');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by location
        if ($request->filled('location_id')) {
            $query->where('current_location_id', $request->location_id);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $assets = $query->paginate(20);
        $locations = MdmOrganizationUnit::where('is_active', true)->get();

        return view('masterdatamanagement::assets.index', compact('assets', 'locations'));
    }

    public function create(): View
    {
        $locations = MdmOrganizationUnit::where('is_active', true)->get();
        return view('masterdatamanagement::assets.create', compact('locations'));
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $asset = MdmAsset::create($request->validated());

        return redirect()
            ->route('mdm.assets.index')
            ->with('success', 'Asset berhasil ditambahkan');
    }

    public function edit(MdmAsset $asset): View
    {
        $locations = MdmOrganizationUnit::where('is_active', true)->get();
        return view('masterdatamanagement::assets.edit', compact('asset', 'locations'));
    }

    public function update(UpdateAssetRequest $request, MdmAsset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        return redirect()
            ->route('mdm.assets.index')
            ->with('success', 'Asset berhasil diupdate');
    }

    public function destroy(MdmAsset $asset): RedirectResponse
    {
        // Check if asset has movements
        if ($asset->movements()->exists()) {
            return redirect()
                ->route('mdm.assets.index')
                ->with('error', 'Asset tidak dapat dihapus karena memiliki riwayat perpindahan');
        }

        $asset->delete();

        return redirect()
            ->route('mdm.assets.index')
            ->with('success', 'Asset berhasil dihapus');
    }

    public function move(MoveAssetRequest $request, MdmAsset $asset): RedirectResponse
    {
        $validated = $request->validated();

        // Create movement record
        MdmAssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $asset->current_location_id,
            'to_location_id' => $validated['to_location_id'],
            'movement_date' => $validated['movement_date'],
            'reason' => $validated['reason'] ?? null,
            'approved_by' => $validated['approved_by'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Update asset location
        $asset->update([
            'current_location_id' => $validated['to_location_id'],
        ]);

        return redirect()
            ->route('mdm.assets.index')
            ->with('success', 'Asset berhasil dipindahkan');
    }

    public function depreciationReport(Request $request): View
    {
        $asOfDate = $request->filled('as_of_date') 
            ? Carbon::parse($request->as_of_date)
            : Carbon::now();

        $assets = MdmAsset::where('is_active', true)
            ->whereNotNull('useful_life_years')
            ->whereNotNull('depreciation_method')
            ->with('currentLocation')
            ->get();

        $depreciationData = $assets->map(function ($asset) use ($asOfDate) {
            return [
                'asset' => $asset,
                'monthly_depreciation' => $this->depreciationService->calculateMonthlyDepreciation($asset),
                'accumulated_depreciation' => $this->depreciationService->calculateAccumulatedDepreciation($asset, $asOfDate),
                'book_value' => $this->depreciationService->getBookValue($asset, $asOfDate),
            ];
        });

        return view('masterdatamanagement::assets.depreciation-report', compact('depreciationData', 'asOfDate'));
    }
}
