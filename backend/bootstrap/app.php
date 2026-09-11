<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'seller' => \App\Http\Middleware\EnsureUserHasRole::class . ':seller',
            'admin' => \App\Http\Middleware\EnsureUserHasRole::class . ':admin',
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'seller.approved' => \App\Http\Middleware\SellerApproved::class,
        ]);

        // Logged-in users hitting guest pages must go to their dashboard,
        // not back to "/" (which redirects to login) — that caused a redirect loop.
        \Illuminate\Auth\Middleware\RedirectIfAuthenticated::redirectUsing(
            fn (Request $request) => $request->user()?->dashboardUrl() ?? '/'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
