<?php

namespace App\Services\Facturas;

use App\Models\facturaModel;
use Carbon\Carbon;

/**
 * Service simplificado para manejo de fechas en facturas
 * Usa funciones nativas de Carbon
 */
class FacturaFechaService
{
    /**
     * Formatea fecha de factura usando Carbon directamente
     */
    public function formatearFechaFactura($factura)
    {
        // Formatear fecha principal
        if ($factura->fecha) {
            $fecha = $factura->fecha instanceof Carbon 
                ? $factura->fecha 
                : Carbon::parse($factura->fecha);
            $factura->setAttribute('fecha', $fecha->format('d/m/Y'));
            $factura->fecha_formateada = $fecha->format('d/m/Y');
        }
        
        $factura->total_items = $factura->detalles->count();
        $factura->total_productos = $factura->detalles->sum('cantidad');
        
        return $factura;
    }

    /**
     * Convierte fecha a formato BD usando Carbon directamente
     */
    public function convertirFechaParaBD($fecha)
    {
        if (empty($fecha)) {
            return null;
        }

        // Si ya está en formato Y-m-d, retornarlo
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }

        // Intentar parsear con Carbon (acepta múltiples formatos)
        try {
            return Carbon::parse($fecha)->format('Y-m-d');
        } catch (\Exception $e) {
            // Si falla, intentar formato d/m/Y específicamente
            try {
                return Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
}
