<?php

namespace Modules\AdminCenter\Http\Controllers;

use App\Models\Module;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function update(Request $request, Module $module): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'entry_route' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

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

    public function toggle(Request $request, Module $module): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $module->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()
            ->route('ac.modules.index')
            ->with('status', 'Module visibility updated.');
    }
}
