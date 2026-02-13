<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuleEntryController extends Controller
{
    public function enter(Request $request, string $moduleKey): RedirectResponse
    {
        $module = $request->attributes->get('activeModule');

        if (! $module instanceof Module) {
            $module = Module::query()->where('key', $moduleKey)->firstOrFail();
        }

        return redirect()->route($module->entry_route);
    }
}
