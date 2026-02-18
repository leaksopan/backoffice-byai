<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\MasterDataManagement\Http\Requests\StoreOrganizationUnitRequest;
use Modules\MasterDataManagement\Http\Requests\UpdateOrganizationUnitRequest;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Services\OrganizationHierarchyService;

class OrganizationUnitController extends MdmBaseController
{
    protected OrganizationHierarchyService $hierarchyService;

    public function __construct(OrganizationHierarchyService $hierarchyService)
    {
        $this->hierarchyService = $hierarchyService;
    }

    public function index(): View
    {
        $units = MdmOrganizationUnit::with('parent')
            ->orderBy('hierarchy_path')
            ->paginate(20);

        return view('masterdatamanagement::organization-units.index', compact('units'));
    }

    public function create(): View
    {
        $parentUnits = MdmOrganizationUnit::active()->orderBy('name')->get();
        $types = ['installation', 'department', 'unit', 'section'];

        return view('masterdatamanagement::organization-units.create', compact('parentUnits', 'types'));
    }

    public function store(StoreOrganizationUnitRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Validate circular reference
        if (isset($validated['parent_id'])) {
            // Untuk unit baru, gunakan ID sementara (0) karena belum ada
            if (!$this->hierarchyService->validateNoCircularReference(0, $validated['parent_id'])) {
                return back()->withErrors(['parent_id' => 'Tidak dapat menetapkan parent karena akan membuat circular reference'])->withInput();
            }
        }

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);

        $unit = MdmOrganizationUnit::create($validated);

        // Update hierarchy path
        $this->hierarchyService->updateHierarchyPath($unit);

        return redirect()
            ->route('mdm.organization-units.index')
            ->with('success', 'Unit organisasi berhasil dibuat');
    }

    public function edit(MdmOrganizationUnit $unit): View
    {
        $parentUnits = MdmOrganizationUnit::active()
            ->where('id', '!=', $unit->id)
            ->orderBy('name')
            ->get();
        $types = ['installation', 'department', 'unit', 'section'];

        return view('masterdatamanagement::organization-units.edit', compact('unit', 'parentUnits', 'types'));
    }

    public function update(UpdateOrganizationUnitRequest $request, MdmOrganizationUnit $unit): RedirectResponse
    {
        $validated = $request->validated();

        // Validate circular reference jika parent berubah
        if (isset($validated['parent_id']) && $validated['parent_id'] != $unit->parent_id) {
            if (!$this->hierarchyService->validateNoCircularReference($unit->id, $validated['parent_id'])) {
                return back()->withErrors(['parent_id' => 'Tidak dapat menetapkan parent karena akan membuat circular reference'])->withInput();
            }
        }

        // Check inactive entity prevention
        if (isset($validated['is_active']) && !$validated['is_active']) {
            // Cek apakah unit ini digunakan (punya children atau digunakan di transaksi)
            if ($unit->children()->count() > 0) {
                return back()->withErrors(['is_active' => 'Unit tidak dapat dinonaktifkan karena masih memiliki child units'])->withInput();
            }
        }

        $validated['updated_by'] = auth()->id();
        $unit->update($validated);

        // Update hierarchy path jika parent berubah
        if (isset($validated['parent_id']) && $validated['parent_id'] != $unit->parent_id) {
            $this->hierarchyService->updateHierarchyPath($unit);
        }

        return redirect()
            ->route('mdm.organization-units.index')
            ->with('success', 'Unit organisasi berhasil diupdate');
    }

    public function destroy(MdmOrganizationUnit $unit): RedirectResponse
    {
        // Check if can delete (no children)
        if (!$this->hierarchyService->canDelete($unit)) {
            return back()->withErrors(['delete' => 'Unit tidak dapat dihapus karena masih memiliki child units']);
        }

        $unit->delete();

        return redirect()
            ->route('mdm.organization-units.index')
            ->with('success', 'Unit organisasi berhasil dihapus');
    }

    /**
     * Get tree structure untuk visualisasi hierarki
     */
    public function tree(): JsonResponse
    {
        $units = MdmOrganizationUnit::with('children')->whereNull('parent_id')->get();

        $tree = $units->map(function ($unit) {
            return $this->buildTreeNode($unit);
        });

        return response()->json($tree);
    }

    private function buildTreeNode(MdmOrganizationUnit $unit): array
    {
        return [
            'id' => $unit->id,
            'code' => $unit->code,
            'name' => $unit->name,
            'type' => $unit->type,
            'is_active' => $unit->is_active,
            'children' => $unit->children->map(fn($child) => $this->buildTreeNode($child))->toArray(),
        ];
    }
}
