<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\productosController;
use App\Http\Controllers\facturaController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\proveedoresController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\DetalleVentasController;
use App\Http\Controllers\usuariosController;
use App\Http\Controllers\DetalleFacturaController;

// Rutas para Productos
Route::patch('productos/{id}/cambiar-estado', [productosController::class, 'cambiarEstado'])->name('productos.cambiar-estado');
Route::get('productos/buscar', [productosController::class, 'buscar'])->name('productos.buscar');
Route::get('productos/disponibles', [productosController::class, 'disponibles'])->name('productos.disponibles');
Route::get('productos/ordenar', [productosController::class, 'ordenar'])->name('productos.ordenar');
Route::apiResource('productos', productosController::class);

// Rutas para Facturas
// MODIFICADO: 2025-11-24 - Solo 4 endpoints esenciales: listar, crear, actualizar, eliminar
Route::prefix('facturas')->group(function () {
    Route::get('/', [facturaController::class, 'listar'])->name('facturas.listar'); // Listar facturas con detalles
    Route::post('/', [facturaController::class, 'store'])->name('facturas.crear'); // Generar/Crear factura con detalles
    Route::put('/{id}', [facturaController::class, 'actualizar'])->name('facturas.actualizar'); // Actualizar factura y detalles
    Route::delete('/{id}', [facturaController::class, 'eliminar'])->name('facturas.eliminar'); // Eliminar factura
});

// Rutas para Clientes
Route::patch('clientes/{id}/estado', [ClientesController::class, 'cambiarEstado']);
Route::get('clientes/buscar', [ClientesController::class, 'buscarPorNombre']);
Route::get('clientes/activos', [ClientesController::class, 'listarActivos']);
Route::apiResource('clientes', ClientesController::class);

// Rutas para Proveedores
// MODIFICADO: 2025-11-25 - Reorganizado para usar apiResource y evitar conflictos de cache
Route::patch('proveedores/{id}/estado', [proveedoresController::class, 'cambiarEstado']);
Route::get('proveedores/buscar', [proveedoresController::class, 'buscarPorNombre']);
Route::get('proveedores/activos', [proveedoresController::class, 'listarActivos']);
Route::apiResource('proveedores', proveedoresController::class);

// Rutas para Ventas
Route::get('ventas/historial', [VentasController::class, 'historial']);
Route::apiResource('ventas', VentasController::class);

// Rutas para Reportes
Route::get('reportes/ventas', [DetalleVentasController::class, 'generarReporteVentas']);


// Rutas para Autenticación y Usuarios
// MODIFICADO: 2025-11-24 - Solo login funciona, resto sin autenticación
Route::post('login', [usuariosController::class, 'login']);
Route::post('register', [usuariosController::class, 'register']);
Route::get('user', [usuariosController::class, 'user']);
Route::post('logout', [usuariosController::class, 'logout']);
Route::apiResource('usuarios', usuariosController::class);