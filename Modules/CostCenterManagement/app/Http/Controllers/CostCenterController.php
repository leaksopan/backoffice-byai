<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\CostCenterManagement\Models\CostCenter;

class CostCenterController extends Controller
{
    public function index(Request $request): View
    {
        return view('costcentermanagement::cost-centers.index');
    }

    public function create(): View
    {
        return view('costcentermanagement::cost-centers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Handled by Livewire
        return redirect()->route('ccm.cost-centers.index');
    }

    public function edit(CostCenter $costCenter): View
    {
        return view('costcentermanagement::cost-centers.edit', compact('costCenter'));
    }

    public function update(Request $request, CostCenter $costCenter): RedirectResponse
    {
        // Handled by Livewire
        return redirect()->route('ccm.cost-centers.index');
    }

    public function destroy(CostCenter $costCenter): RedirectResponse
    {
        if ($costCenter->children()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'Tidak bisa hapus cost center yang masih punya child');
        }

        $costCenter->delete();

        return redirect()
            ->route('ccm.cost-centers.index')
            ->with('success', 'Cost center berhasil dihapus');
    }

    public function tree(): View
    {
        return view('costcentermanagement::cost-centers.tree');
    }
}
