<?php

namespace App\Http\Controllers;

use App\Models\facturaModel;
use App\Models\productosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleFacturaModel;
use OpenApi\Attributes as OA;
class facturaController extends Controller
{
    public function index()
    {
        $facturas = facturaModel::with('cliente')->paginate(10);
        return response()->json(['status' => 'success', 'data' => $facturas]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $factura = facturaModel::create([
                'cliente_id' => $request->cliente_id,
                'numero_facturas' => 'F-' . uniqid(),
                'fecha' => now(),
                'total' => 0, // Se calculará después
                'estado' => 'pendiente',
            ]);

            $total = 0;
            foreach ($request->detalles as $detalle) {
                $producto = productosModel::find($detalle['producto_id']);
                if (!$producto || $producto->cantidad_disponible < $detalle['cantidad']) {
                    throw new \Exception('Stock no disponible');
                }

                $subtotal = $producto->precio * $detalle['cantidad'];
                $total += $subtotal;

                $factura->detalles()->create([
                    'producto_id' => $producto->IdProducto,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);

                $producto->cantidad_disponible -= $detalle['cantidad'];
                $producto->save();
            }

            $factura->total = $total;
            $factura->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Factura creada exitosamente',
                'data' => $factura->load('detalles')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al crear la factura', 'error' => $e->getMessage()], 500);
        }
    }

    public function mostrar($id)
    {
        $factura = facturaModel::with(['cliente', 'detalles.producto'])->find($id);

        if (!$factura) {
            return response()->json(['status' => 'error', 'message' => 'Factura no encontrada'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $factura]);
    }
    /**
     * Actualizar factura (campo simples como cliente_id, numero_facturas, estado)
     */
    public function actualizar($id, Request $request)
    {
        $factura = facturaModel::find($id);
        if (!$factura) {
            return response()->json(['status' => 'error', 'message' => 'Factura no encontrada'], 404);
        }

        $data = $request->only(['cliente_id', 'numero_facturas', 'fecha', 'estado', 'total']);

        // Validaciones básicas
        $request->validate([
            'cliente_id' => 'sometimes|integer|exists:clientes,id',
            'numero_facturas' => 'sometimes|string',
            'fecha' => 'sometimes|date',
            'estado' => 'sometimes',
            'total' => 'sometimes|numeric',
        ]);

        $factura->fill($data);
        $factura->save();

        return response()->json(['status' => 'success', 'message' => 'Factura actualizada', 'data' => $factura]);
    }

    public function destruir($id)
    {
        $factura = facturaModel::find($id);

        if (!$factura) {
            return response()->json(['status' => 'error', 'message' => 'Factura no encontrada'], 404);
        }

        // Se eliminan los detalles asociados a la factura
        $factura->detalles()->delete();
        $factura->delete(); // Borrado permanente

        return response()->json(['status' => 'success', 'message' => 'Factura eliminada permanentemente']);
    }
}