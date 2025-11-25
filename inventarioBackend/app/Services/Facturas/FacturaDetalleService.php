<?php

namespace App\Services\Facturas;

use App\Models\DetalleFacturaModel;
use App\Models\ProductosModel;
use Illuminate\Support\Facades\Log;

/**
 * Service para manejo de detalles de factura
 * 
 * MODIFICADO: 2025-11-24
 * Cambio: Extraído de FacturaService para modularizar código
 * Razón: FacturaService tenía más de 500 líneas, se separó la lógica de detalles
 */
class FacturaDetalleService
{
    /**
     * Valida stock y crea detalle de factura
     * 
     * MODIFICADO: 2025-11-24
     * Cambio: Eliminada actualización del total de factura (se hace en crearFactura)
     * Razón: El método crearFactura calcula y actualiza el total, evitar duplicación
     */
    public function crearDetalle($factura, $productoId, $cantidad)
    {
        // Buscar producto por IdProducto (clave primaria)
        $producto = ProductosModel::find($productoId);
        
        if (!$producto) {
            throw new \Exception("Producto con ID {$productoId} no encontrado");
        }
        
        if ($producto->cantidad_disponible < $cantidad) {
            throw new \Exception("Stock insuficiente. Disponible: {$producto->cantidad_disponible}, Solicitado: {$cantidad}");
        }

        $precio = $producto->precio;
        $subtotal = $precio * $cantidad;

        $detalle = DetalleFacturaModel::create([
            'factura_id' => $factura->id,
            'producto_id' => $producto->IdProducto,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $subtotal,
        ]);

        // Actualizar stock del producto
        $producto->cantidad_disponible -= $cantidad;
        $producto->save();

        // NOTA: El total de la factura se actualiza en crearFactura, no aquí
        // para evitar múltiples actualizaciones innecesarias

        return $detalle;
    }

    /**
     * Actualiza los detalles de una factura
     * 
     * MODIFICADO: 2025-11-24
     * Cambio: Método agregado para actualizar detalles de factura
     * Razón: Permitir actualizar detalles junto con la factura
     * 
     * @param facturaModel $factura
     * @param array $detalles
     * @return void
     */
    public function actualizarDetalles($factura, $detalles)
    {
        $detallesIds = [];

        foreach ($detalles as $detalleData) {
            // Si tiene ID, es una actualización de un detalle existente
            if (isset($detalleData['id'])) {
                $detalle = DetalleFacturaModel::where('id', $detalleData['id'])
                    ->where('factura_id', $factura->id)
                    ->first();

                if ($detalle) {
                    // Guardar valores originales antes de modificar
                    $productoIdOriginal = $detalle->producto_id;
                    $cantidadOriginal = $detalle->cantidad;

                    // Actualizar detalle
                    if (isset($detalleData['cantidad']) && isset($detalleData['producto_id'])) {
                        $producto = ProductosModel::find($detalleData['producto_id']);
                        if (!$producto) {
                            throw new \Exception("Producto con ID {$detalleData['producto_id']} no encontrado");
                        }

                        // Restaurar stock del producto original PRIMERO
                        $productoAnterior = ProductosModel::find($productoIdOriginal);
                        if ($productoAnterior) {
                            $productoAnterior->cantidad_disponible += $cantidadOriginal;
                            $productoAnterior->save();
                        }
                        
                        // Si es el mismo producto, recargar para tener el stock actualizado
                        $productoCambio = ($productoIdOriginal != $producto->IdProducto);
                        if (!$productoCambio) {
                            $producto->refresh();
                        }
                        
                        // Validar stock DESPUÉS de restaurar
                        if ($producto->cantidad_disponible < $detalleData['cantidad']) {
                            // Revertir restauración si falla
                            if ($productoAnterior) {
                                $productoAnterior->cantidad_disponible -= $cantidadOriginal;
                                $productoAnterior->save();
                            }
                            throw new \Exception("Stock insuficiente para producto {$producto->nombre}. Disponible: {$producto->cantidad_disponible}, Solicitado: {$detalleData['cantidad']}");
                        }

                        // Calcular nuevo subtotal
                        $precio = $producto->precio;
                        $subtotal = $precio * $detalleData['cantidad'];

                        // Actualizar detalle
                        $detalle->producto_id = $producto->IdProducto;
                        $detalle->cantidad = $detalleData['cantidad'];
                        $detalle->precio_unitario = $precio;
                        $detalle->subtotal = $subtotal;
                        $detalle->save();

                        // Actualizar stock del producto nuevo/actualizado
                        if ($productoCambio) {
                            // Producto cambió: descontar cantidad completa del nuevo producto
                            $producto->cantidad_disponible -= $detalleData['cantidad'];
                        } else {
                            // Mismo producto: ajustar solo la diferencia
                            $diferenciaCantidad = $detalleData['cantidad'] - $cantidadOriginal;
                            $producto->cantidad_disponible -= $diferenciaCantidad;
                        }
                        $producto->save();

                        $detallesIds[] = $detalle->id;
                    } else {
                        // Si no tiene cantidad o producto_id, eliminar el detalle
                        // Restaurar stock antes de eliminar
                        $productoAnterior = ProductosModel::find($productoIdOriginal);
                        if ($productoAnterior) {
                            $productoAnterior->cantidad_disponible += $cantidadOriginal;
                            $productoAnterior->save();
                        }
                        $detalle->delete();
                    }
                }
            } else {
                // Es un nuevo detalle
                if (isset($detalleData['producto_id']) && isset($detalleData['cantidad'])) {
                    $detalleCreado = $this->crearDetalle($factura, $detalleData['producto_id'], $detalleData['cantidad']);
                    $detallesIds[] = $detalleCreado->id;
                }
            }
        }

        // Eliminar detalles que no están en la lista enviada
        DetalleFacturaModel::where('factura_id', $factura->id)
            ->whereNotIn('id', $detallesIds)
            ->get()
            ->each(function ($detalle) {
                $this->restaurarStock($detalle);
                $detalle->delete();
            });

        // Recalcular total de la factura sumando todos los subtotales
        $factura->total = DetalleFacturaModel::where('factura_id', $factura->id)->sum('subtotal');
        $factura->save();
    }

    /**
     * Restaura stock al eliminar detalle
     */
    public function restaurarStock($detalle)
    {
        $producto = ProductosModel::find($detalle->producto_id);
        if ($producto) {
            $producto->cantidad_disponible += $detalle->cantidad;
            $producto->save();
        }
    }
}

