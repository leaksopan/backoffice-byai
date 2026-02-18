<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

class ServiceCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = MdmServiceCatalog::with('unit');

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('unit_id')) {
            $query->byUnit($request->unit_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        $services = $query->paginate(20);
        $units = MdmOrganizationUnit::active()->get();

        return view('masterdatamanagement::services.index', compact('services', 'units'));
    }

    public function create(): View
    {
        $units = MdmOrganizationUnit::active()->get();
        return view('masterdatamanagement::services.create', compact('units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:mdm_service_catalogs,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:rawat_jalan,rawat_inap,igd,penunjang_medis,tindakan,operasi,persalinan,administrasi',
            'unit_id' => 'required|exists:mdm_organization_units,id',
            'inacbg_code' => 'nullable|string|max:50',
            'standard_duration' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        MdmServiceCatalog::create($validated);

        return redirect()->route('mdm.services.index')
            ->with('success', 'Service catalog berhasil dibuat');
    }

    public function edit(MdmServiceCatalog $service): View
    {
        $units = MdmOrganizationUnit::active()->get();
        return view('masterdatamanagement::services.edit', compact('service', 'units'));
    }

    public function update(Request $request, MdmServiceCatalog $service): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:mdm_service_catalogs,code,' . $service->id,
            'name' => 'required|string|max:255',
            'category' => 'required|in:rawat_jalan,rawat_inap,igd,penunjang_medis,tindakan,operasi,persalinan,administrasi',
            'unit_id' => 'required|exists:mdm_organization_units,id',
            'inacbg_code' => 'nullable|string|max:50',
            'standard_duration' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $service->update($validated);

        return redirect()->route('mdm.services.index')
            ->with('success', 'Service catalog berhasil diupdate');
    }

    public function destroy(MdmServiceCatalog $service): RedirectResponse
    {
        // Check if service is used in tariffs
        if ($service->tariffs()->exists()) {
            return redirect()->route('mdm.services.index')
                ->with('error', 'Service tidak dapat dihapus karena masih digunakan dalam tarif');
        }

        $service->delete();

        return redirect()->route('mdm.services.index')
            ->with('success', 'Service catalog berhasil dihapus');
    }

    public function searchByCode(string $code): JsonResponse
    {
        $service = MdmServiceCatalog::with('unit')
            ->where('code', $code)
            ->active()
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }
}
