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
    public function handle(Request $request, Closure $next, ?string $moduleKey = null): Response
    {
        $moduleKey = $this->resolveModuleKey($request);

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

    private function resolveModuleKey(Request $request): ?string
    {
        $fromRoute = $request->route('moduleKey');

        if (is_string($fromRoute) && $fromRoute !== '') {
            return $fromRoute;
        }

        $first = $request->segment(1);
        $second = $request->segment(2);

        if ($first === 'm' && is_string($second) && $second !== '') {
            return $second;
        }

        return null;
    }
}
