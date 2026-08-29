<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user() || !$request->user()->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'You do not have permission to access this resource.',
                    ],
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
