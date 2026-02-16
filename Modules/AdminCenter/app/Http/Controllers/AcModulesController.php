<?php

namespace Modules\AdminCenter\Http\Controllers;

use App\Models\Module;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\AdminCenter\Http\Requests\ToggleModuleRequest;
use Modules\AdminCenter\Http\Requests\UpdateModuleRequest;

class AcModulesController
{
    public function index(): View
    {
        $modules = Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admincenter::modules.index', [
            'modules' => $modules,
        ]);
    }

    public function edit(Module $module): View
    {
        return view('admincenter::modules.edit', [
            'module' => $module,
        ]);
    }

    public function update(UpdateModuleRequest $request, Module $module): RedirectResponse
    {
        $validated = $request->validated();

        $module->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'entry_route' => $validated['entry_route'],
            'sort_order' => $validated['sort_order'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('ac.modules.index')
            ->with('status', 'Module updated successfully.');
    }

    public function toggle(ToggleModuleRequest $request, Module $module): RedirectResponse
    {
        $validated = $request->validated();

        $module->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()
            ->route('ac.modules.index')
            ->with('status', 'Module visibility updated.');
    }
}
