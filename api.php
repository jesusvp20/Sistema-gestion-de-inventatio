<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para el CRUD de Clientes
Route::get('/clientes', [ClientesController::class, 'listar']);
Route::post('/clientes', [ClientesController::class, 'crear']);
Route::put('/clientes/{id}', [ClientesController::class, 'actualizar']);
Route::delete('/clientes/{id}', [ClientesController::class, 'eliminar']);
Route::patch('/clientes/{id}/estado', [ClientesController::class, 'cambiarEstado']);
Route::get('/clientes/buscar', [ClientesController::class, 'buscarPorNombre']);
Route::get('/clientes/activos', [ClientesController::class, 'listarActivos']);