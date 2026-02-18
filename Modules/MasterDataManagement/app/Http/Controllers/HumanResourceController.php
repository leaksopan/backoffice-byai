<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\MasterDataManagement\Models\MdmHumanResource;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Http\Requests\StoreHumanResourceRequest;
use Modules\MasterDataManagement\Http\Requests\UpdateHumanResourceRequest;
use Modules\MasterDataManagement\Http\Requests\StoreHrAssignmentRequest;

class HumanResourceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MdmHumanResource::query()->with('activeAssignments.organizationUnit');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by unit
        if ($request->filled('unit_id')) {
            $query->whereHas('activeAssignments', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by NIP or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $humanResources = $query->paginate(20);
        $units = MdmOrganizationUnit::where('is_active', true)->get();

        return view('masterdatamanagement::human-resources.index', compact('humanResources', 'units'));
    }

    public function create(): View
    {
        return view('masterdatamanagement::human-resources.create');
    }

    public function store(StoreHumanResourceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        MdmHumanResource::create($data);

        return redirect()
            ->route('mdm.human-resources.index')
            ->with('success', 'Data SDM berhasil ditambahkan');
    }

    public function edit(MdmHumanResource $humanResource): View
    {
        return view('masterdatamanagement::human-resources.edit', compact('humanResource'));
    }

    public function update(UpdateHumanResourceRequest $request, MdmHumanResource $humanResource): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $humanResource->update($data);

        return redirect()
            ->route('mdm.human-resources.index')
            ->with('success', 'Data SDM berhasil diperbarui');
    }

    public function destroy(MdmHumanResource $humanResource): RedirectResponse
    {
        try {
            $humanResource->delete();
            return redirect()
                ->route('mdm.human-resources.index')
                ->with('success', 'Data SDM berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()
                ->route('mdm.human-resources.index')
                ->with('error', 'Data SDM tidak dapat dihapus karena masih digunakan');
        }
    }

    public function assignments(MdmHumanResource $humanResource): View
    {
        $humanResource->load('assignments.organizationUnit');
        $units = MdmOrganizationUnit::where('is_active', true)->get();

        return view('masterdatamanagement::human-resources.assignments', compact('humanResource', 'units'));
    }

    public function storeAssignment(StoreHrAssignmentRequest $request, MdmHumanResource $humanResource): RedirectResponse
    {
        $humanResource->assignments()->create($request->validated());

        return redirect()
            ->route('mdm.human-resources.assignments', $humanResource)
            ->with('success', 'Penugasan berhasil ditambahkan');
    }
}
