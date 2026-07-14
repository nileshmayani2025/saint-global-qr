<?php

use App\Http\Middleware\CaptureRequestContext;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Capture audit context (IP / device / browser) on every request.
        $middleware->append(CaptureRequestContext::class);

        // Force JSON responses for the API stack.
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        // Route-level aliases.
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'no.cache' => \App\Http\Middleware\PreventPageCaching::class,
            'approved' => \App\Http\Middleware\EnsureApprovedToScan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
