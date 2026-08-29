<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->isActive()) {
            if ($request->expectsJson()) {
                auth()->guard('web')->logout();
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ACCOUNT_SUSPENDED',
                        'message' => 'Your account has been suspended.',
                    ],
                ], 403);
            }
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been suspended.');
        }

        return $next($request);
    }
}
