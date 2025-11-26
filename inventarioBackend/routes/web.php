<?php

use Illuminate\Support\Facades\Route;

/**
 * MODIFICADO: 2025-01-27
 * Cambio: Reemplazado view('welcome') por respuesta JSON
 * Razón: La aplicación es API-only y no tiene configuración de vistas, causando error 500
 */
Route::get('/', function () {
    return response()->json([
        'message' => 'Sistema de Gestión de Inventario API',
        'version' => '1.0.0',
        'status' => 'active',
        'documentation' => '/api/documentation',
        'health' => '/up'
    ]);
});
