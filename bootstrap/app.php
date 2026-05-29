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
        $middleware->alias([
            'AdminAuth' => \App\Http\Middleware\AdminAuth::class,
            'VendorAuth' => \App\Http\Middleware\VendorAuth::class,
            'inject.bearer' => \App\Http\Middleware\InjectBearerTokenFromInput::class,
            'set.customer.locale' => \App\Http\Middleware\SetCustomerLocale::class,
            'driver' => \App\Http\Middleware\EnsureDriverUser::class,
            'customer.active' => \App\Http\Middleware\EnsureActiveCustomerUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
