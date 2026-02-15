<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Core module guard for all `/m/{moduleKey}` routes.
 * Validates module existence, active status, and `access {moduleKey}` permission.
 */
class EnsureModuleAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $moduleKey = $request->route('moduleKey');

        if (! $moduleKey) {
            abort(404);
        }

        $module = Module::query()->where('key', $moduleKey)->first();

        if (! $module) {
            abort(404);
        }

        if (! $module->is_active) {
            abort(403);
        }

        $user = $request->user();

        if (! $user || ! $user->can('access '.$moduleKey)) {
            abort(403);
        }

        $request->attributes->set('activeModule', $module);

        return $next($request);
    }
}
