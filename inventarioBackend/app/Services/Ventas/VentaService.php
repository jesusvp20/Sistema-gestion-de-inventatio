<?php

namespace App\Services\Ventas;

use App\Models\ventasModel;
use App\Models\ProductosModel;
use Carbon\Carbon;

/**
 * Service simplificado para lógica de negocio de ventas
 * Usa funciones nativas de Carbon
 */
class VentaService
{
    /**
     * Formatea fechas en venta usando Carbon directamente
     */
    public function formatearFechasVenta($venta)
    {
        // Formatear fecha_venta
        if ($venta->fecha_venta) {
            $fecha = $venta->fecha_venta instanceof Carbon 
                ? $venta->fecha_venta 
                : Carbon::parse($venta->fecha_venta);
            $venta->setAttribute('fecha_venta', $fecha->format('d/m/Y'));
        }
        
        // Formatear fechas en productos de los detalles
        if ($venta->detalles) {
            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto) {
                    $this->formatearFechasProducto($detalle->producto);
                }
            }
        }
        
        return $venta;
    }

    /**
     * Formatea fechas de producto usando Carbon directamente
     */
    private function formatearFechasProducto($producto)
    {
        if ($producto->fecha_creacion) {
            $fecha = $producto->fecha_creacion instanceof Carbon 
                ? $producto->fecha_creacion 
                : Carbon::parse($producto->fecha_creacion);
            $producto->setAttribute('fecha_creacion', $fecha->format('d/m/Y'));
        }
        
        if ($producto->fecha_actualizacion) {
            $fecha = $producto->fecha_actualizacion instanceof Carbon 
                ? $producto->fecha_actualizacion 
                : Carbon::parse($producto->fecha_actualizacion);
            $producto->setAttribute('fecha_actualizacion', $fecha->format('d/m/Y'));
        }
    }

    /**
     * Valida y procesa detalles de venta
     */
    public function procesarDetalles($detalles)
    {
        $totalVenta = 0;
        $detallesData = [];

        foreach ($detalles as $detalle) {
            $producto = ProductosModel::find($detalle['id_producto']);

            if (!$producto) {
                throw new \Exception("Producto con ID {$detalle['id_producto']} no encontrado");
            }

            if ($producto->cantidad_disponible < $detalle['cantidad']) {
                throw new \Exception(
                    'No hay suficiente stock disponible. El producto "' . $producto->nombre . 
                    '" tiene ' . $producto->cantidad_disponible . ' unidades disponibles y se solicitaron ' . $detalle['cantidad']
                );
            }

            $subtotal = $producto->precio * $detalle['cantidad'];
            $totalVenta += $subtotal;

            // Actualizar stock
            $producto->cantidad_disponible -= $detalle['cantidad'];
            $producto->save();

            $detallesData[] = [
                'id_producto' => $producto->IdProducto,
                'cantidad' => $detalle['cantidad'],
                'precio' => $producto->precio,
            ];
        }

        return ['total' => $totalVenta, 'detalles' => $detallesData];
    }
}
