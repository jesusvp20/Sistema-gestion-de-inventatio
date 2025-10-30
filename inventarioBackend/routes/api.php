<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\productosController;
use App\Http\Controllers\facturaController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\DetalleVentasController;
use App\Http\Controllers\usuariosController;
use App\Http\Controllers\DetalleFacturaController;

// Rutas para Productos
Route::patch('productos/{id}/cambiar-estado', [productosController::class, 'cambiarEstado'])->name('productos.cambiar-estado');
Route::get('productos/buscar', [productosController::class, 'buscar'])->name('productos.buscar');
Route::get('productos/activos', [productosController::class, 'activos'])->name('productos.activos');
Route::get('productos/ordenar', [productosController::class, 'ordenar'])->name('productos.ordenar');
Route::apiResource('productos', productosController::class);

// Rutas para Facturas y Detalles
Route::prefix('facturas')->group(function () {
    // Operaciones principales de facturas
    Route::get('/', [DetalleFacturaController::class, 'listarFacturas'])->name('facturas.listar');
    Route::get('/{id}', [DetalleFacturaController::class, 'mostrarFactura'])->name('facturas.mostrar');
    Route::post('/', [DetalleFacturaController::class, 'crearFactura'])->name('facturas.crear');
    
    // Operaciones de detalles
    Route::post('/{id}/detalles', [DetalleFacturaController::class, 'agregarDetalle'])->name('facturas.detalles.crear');
    Route::put('/detalles/{id}', [DetalleFacturaController::class, 'actualizarDetalle'])->name('facturas.detalles.actualizar');
    Route::delete('/detalles/{id}', [DetalleFacturaController::class, 'eliminarDetalle'])->name('facturas.detalles.eliminar');
    
    // Endpoints auxiliares para crear facturas
    Route::get('/proximo-numero', [DetalleFacturaController::class, 'proximoNumero'])->name('facturas.proximo-numero');
    Route::post('/validar-detalles', [DetalleFacturaController::class, 'validarDetalles'])->name('facturas.validar-detalles');
    
    // Endpoints de consulta
    Route::get('/productos/listar', [DetalleFacturaController::class, 'listarProductos'])->name('facturas.productos.listar');
    Route::get('/productos/{id}', [DetalleFacturaController::class, 'mostrarProducto'])->name('facturas.productos.mostrar');
    Route::get('/clientes/listar', [DetalleFacturaController::class, 'listarClientes'])->name('facturas.clientes.listar');
});

// Rutas para Clientes
Route::patch('clientes/{id}/estado', [ClientesController::class, 'cambiarEstado']);
Route::get('clientes/buscar', [ClientesController::class, 'buscarPorNombre']);
Route::get('clientes/activos', [ClientesController::class, 'listarActivos']);
Route::apiResource('clientes', ClientesController::class);

// Rutas para Proveedores
Route::patch('proveedores/{id}/estado', [ProveedoresController::class, 'cambiarEstado']);
Route::get('proveedores/buscar', [ProveedoresController::class, 'buscarPorNombre']);
Route::get('proveedores/activos', [ProveedoresController::class, 'listarActivos']);
Route::apiResource('proveedores', ProveedoresController::class);

// Rutas para Ventas
Route::get('ventas/historial', [VentasController::class, 'historial']);
Route::apiResource('ventas', VentasController::class);

// Rutas para Reportes
Route::get('reportes/ventas', [DetalleVentasController::class, 'generarReporteVentas']);

// Rutas para la Gestión de Facturas desde DetalleFacturaController
Route::get('gestion-facturas', [DetalleFacturaController::class, 'listar']);
Route::get('gestion-facturas/{id}', [DetalleFacturaController::class, 'mostrar']);
Route::post('gestion-facturas/{id}/archivar', [DetalleFacturaController::class, 'archivar']);
Route::delete('gestion-facturas/{id}', [DetalleFacturaController::class, 'destruir']);
Route::post('gestion-facturas/archivar-varios', [DetalleFacturaController::class, 'archivarVarios']);
Route::post('gestion-facturas/destruir-varios', [DetalleFacturaController::class, 'destruirVarios']);

// Rutas para Autenticación y Usuarios
Route::post('register', [usuariosController::class, 'register']);
Route::post('login', [usuariosController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [usuariosController::class, 'user']);
    Route::post('logout', [usuariosController::class, 'logout']);
    Route::apiResource('usuarios', usuariosController::class);
});