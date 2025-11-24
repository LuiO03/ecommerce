<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        // aqui se agrega la ruta para el administrador
        then: function (): void {
            //El middleware es para proteger las rutas
            Route::middleware([
                'web',
                'auth:sanctum',
                config('jetstream.auth_session'),
                'verified'
            ])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        }

    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
