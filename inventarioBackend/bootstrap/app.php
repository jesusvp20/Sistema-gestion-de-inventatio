<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configurar CORS para permitir peticiones desde Angular
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // En producción, loguear errores pero no exponer detalles sensibles
        if (app()->environment('production')) {
            $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
                // Para peticiones API, siempre retornar JSON
                return $request->is('api/*') || $request->expectsJson();
            });
        }
    })->create();
