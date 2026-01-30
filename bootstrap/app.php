<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register tenancy middleware
        $middleware->alias([
            'tenant' => \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            'tenant.token' => \App\Http\Middleware\InitializeTenancyByToken::class,
            'tenant.locale' => \App\Http\Middleware\SetTenantLocale::class,
            'super.admin' => \App\Http\Middleware\CheckSuperAdmin::class,

            // Role-based middleware
            'role' => \App\Http\Middleware\CheckRole::class,
            'ability' => \App\Http\Middleware\CheckTokenAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
