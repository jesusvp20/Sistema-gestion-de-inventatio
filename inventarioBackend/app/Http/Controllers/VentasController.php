<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ventasModel;
use App\Models\ProductosModel;
use Illuminate\Support\Facades\DB;

class ventasController extends Controller
{
    /**
     * Lista todas las ventas con su cliente y detalles de productos.
     */
    public function index(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => 'nullable|date_format:Y-m-d',
                'fecha_fin' => 'nullable|date_format:Y-m-d|after_or_equal:fecha_inicio',
                'por_pagina' => 'nullable|integer|min:1',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Filtros inválidos.',
                    'errors' => $validator->errors()
                ], 400);
            }
    
            $query = ventasModel::with('cliente', 'detalles.producto');
    
            if ($request->has('fecha_inicio')) {
                $query->whereDate('fecha_venta', '>=', $request->input('fecha_inicio'));
            }
    
            if ($request->has('fecha_fin')) {
                $query->whereDate('fecha_venta', '<=', $request->input('fecha_fin'));
            }
    
            $porPagina = $request->input('por_pagina', 15);
            $ventas = $query->orderBy('fecha_venta', 'desc')->paginate($porPagina);
    
            if($ventas->isEmpty()){
                return response()->json([
                    'status'=> 'error',
                    'message' => 'No hay ventas registradas que coincidan con los filtros.',
                ], 404);
            }
            return response()->json([
                'status'=> 'success',
                'data' => $ventas
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status'=> 'error',
                'message' => 'Error al listar ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea una nueva venta y sus detalles.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|integer|exists:clientes,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|integer|exists:producto,IdProducto',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Usar una transacción para asegurar la integridad de los datos
            $venta = DB::transaction(function () use ($request) {
                $totalVenta = 0;
                $detallesData = [];

                foreach ($request->detalles as $detalle) {
                    $producto = ProductosModel::find($detalle['id_producto']);

                    if ($producto->cantidad_disponible < $detalle['cantidad']) {
                        // Lanzar una excepción para revertir la transacción
                        throw new \Exception('No hay suficiente stock para el producto: ' . $producto->nombre);
                    }

                    $subtotal = $producto->precio * $detalle['cantidad'];
                    $totalVenta += $subtotal;

                    // Actualizar stock del producto
                    $producto->cantidad_disponible -= $detalle['cantidad'];
                    $producto->save();

                    $detallesData[] = [
                        'id_producto' => $producto->IdProducto,
                        'cantidad' => $detalle['cantidad'],
                        'precio' => $producto->precio,
                    ];
                }

                $venta = ventasModel::create([
                    'id_cliente' => $request->id_cliente,
                    'total' => $totalVenta,
                ]);

                $venta->detalles()->createMany($detallesData);

                return $venta->load('cliente', 'detalles.producto');
            });

            return response()->json(['status' => 'success', 'message' => 'Venta creada exitosamente', 'data' => $venta], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al crear la venta', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza una venta existente.
     * Esto implica revertir el stock de la venta original y procesar la nueva.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'sometimes|required|integer|exists:clientes,id',
            'detalles' => 'sometimes|required|array|min:1',
            'detalles.*.id_producto' => 'required_with:detalles|integer|exists:producto,IdProducto',
            'detalles.*.cantidad' => 'required_with:detalles|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 400);
        }

        try {
            $ventaActualizada = DB::transaction(function () use ($request, $id) {
                $venta = ventasModel::with('detalles.producto')->findOrFail($id);

                // 1. Revertir el stock de la venta original
                foreach ($venta->detalles as $detalle) {
                    $producto = $detalle->producto;
                    if ($producto) {
                        $producto->cantidad_disponible += $detalle->cantidad;
                        $producto->save();
                    }
                }

                // 2. Eliminar los detalles antiguos
                $venta->detalles()->delete();

                // 3. Procesar los nuevos detalles y calcular el nuevo total
                $totalVenta = 0;
                $nuevosDetallesData = [];

                if ($request->has('detalles')) {
                    foreach ($request->detalles as $detalle) {
                        $producto = ProductosModel::find($detalle['id_producto']);

                        if ($producto->cantidad_disponible < $detalle['cantidad']) {
                            throw new \Exception('No hay suficiente stock para el producto: ' . $producto->nombre);
                        }

                        $subtotal = $producto->precio * $detalle['cantidad'];
                        $totalVenta += $subtotal;

                        // Actualizar stock del producto
                        $producto->cantidad_disponible -= $detalle['cantidad'];
                        $producto->save();

                        $nuevosDetallesData[] = [
                            'id_producto' => $producto->IdProducto,
                            'cantidad' => $detalle['cantidad'],
                            'precio' => $producto->precio,
                        ];
                    }
                }

                // 4. Actualizar la venta
                $venta->update([
                    'id_cliente' => $request->input('id_cliente', $venta->id_cliente),
                    'total' => $totalVenta,
                ]);

                // 5. Crear los nuevos detalles
                if (!empty($nuevosDetallesData)) {
                    $venta->detalles()->createMany($nuevosDetallesData);
                }

                return $venta->load('cliente', 'detalles.producto');
            });

            return response()->json(['status' => 'success', 'message' => 'Venta actualizada exitosamente', 'data' => $ventaActualizada], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Venta no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar la venta', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una venta y restaura el stock de los productos.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $venta = ventasModel::with('detalles.producto')->findOrFail($id);

                // Restaurar el stock de cada producto en la venta
                foreach ($venta->detalles as $detalle) {
                    $producto = $detalle->producto;
                    if ($producto) {
                        $producto->cantidad_disponible += $detalle->cantidad;
                        $producto->save();
                    }
                }

                // Eliminar la venta (los detalles se borrarán en cascada si está configurado,
                // o se pueden borrar manualmente primero: $venta->detalles()->delete();)
                $venta->detalles()->delete();
                $venta->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Venta eliminada exitosamente'], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Venta no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra el historial de ventas con detalles.
     */
    public function historial(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => 'nullable|date_format:Y-m-d',
                'fecha_fin' => 'nullable|date_format:Y-m-d|after_or_equal:fecha_inicio',
                'por_pagina' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Filtros inválidos.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $query = ventasModel::with('cliente', 'detalles.producto');

            if ($request->has('fecha_inicio')) {
                $query->whereDate('fecha_venta', '>=', $request->input('fecha_inicio'));
            }

            if ($request->has('fecha_fin')) {
                $query->whereDate('fecha_venta', '<=', $request->input('fecha_fin'));
            }

            $porPagina = $request->input('por_pagina', 15);
            $ventas = $query->orderBy('fecha_venta', 'desc')->paginate($porPagina);

            if ($ventas->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay ventas registradas que coincidan con los filtros.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $ventas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el historial de ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
