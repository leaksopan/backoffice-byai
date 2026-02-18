<?php

namespace Modules\CostCenterManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthorization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 401);
        }

        if (!auth()->user()->can($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk operasi ini',
                'error' => [
                    'code' => 'FORBIDDEN',
                    'required_permission' => $permission,
                ],
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 403);
        }

        return $next($request);
    }
}
