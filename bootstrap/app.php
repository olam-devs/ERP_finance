<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/superadmin.php'));
            Route::middleware('web')
                ->group(base_path('routes/headmaster.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.role' => \App\Http\Middleware\CheckRole::class,
            'parent.auth' => \App\Http\Middleware\ParentAuthMiddleware::class,
            'tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'superadmin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'headmaster.auth' => \App\Http\Middleware\HeadmasterAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
