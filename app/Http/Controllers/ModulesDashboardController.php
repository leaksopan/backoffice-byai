<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Core module launcher dashboard.
 * Lists active modules ordered by DB sort with permission filtering.
 */
class ModulesDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $modules = Module::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Module $module) => $user && $user->can('access '.$module->key));

        return view('modules.dashboard', [
            'modules' => $modules,
        ]);
    }
}
