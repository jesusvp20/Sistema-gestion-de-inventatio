<?php

namespace App\Services\Facturas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service para aplicar filtros a consultas de facturas
 * 
 * MODIFICADO: 2025-11-24
 * Cambio: Extraído de FacturaService para modularizar código
 * Razón: FacturaService tenía más de 500 líneas, se separó la lógica de filtros
 */
class FacturaFiltroService
{
    protected $fechaService;

    public function __construct(FacturaFechaService $fechaService)
    {
        $this->fechaService = $fechaService;
    }

    /**
     * Aplica filtros a query de facturas
     * 
     * MODIFICADO: 2025-11-24
     * Cambio: Filtros ahora son completamente opcionales y manejan errores de forma segura
     * Razón: Los filtros de fechas causaban error 500 cuando el formato era incorrecto
     * Solución: Validar y convertir fechas antes de aplicar filtros, con manejo de errores
     */
    public function aplicarFiltros($query, $request)
    {
        try {
            // Filtro de fechas - Solo aplicar si ambas fechas están presentes y son válidas
            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $fechaInicio = $this->fechaService->convertirFechaParaBD($request->fecha_inicio);
                $fechaFin = $this->fechaService->convertirFechaParaBD($request->fecha_fin);
                
                if ($fechaInicio && $fechaFin) {
                    $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                } else {
                    Log::warning('FacturaFiltroService::aplicarFiltros - Fechas inválidas, omitiendo filtro de fechas', [
                        'fecha_inicio' => $request->fecha_inicio,
                        'fecha_fin' => $request->fecha_fin,
                    ]);
                }
            }

            // Filtro por cliente - Solo aplicar si está presente
            if ($request->filled('cliente')) {
                $query->whereHas('cliente', function ($q) use ($request) {
                    $q->where('nombre', 'like', "%{$request->cliente}%");
                });
            }

            // Filtro por estado - Solo aplicar si está presente
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            // Ordenamiento - Valores por defecto seguros
            $orderBy = $request->get('order_by', 'fecha');
            $orderDir = strtolower($request->get('order_dir', 'desc'));
            
            // Validar que orderDir sea válido
            if (!in_array($orderDir, ['asc', 'desc'])) {
                $orderDir = 'desc';
            }
            
            // Validar que orderBy sea una columna válida
            $columnasValidas = ['fecha', 'id', 'numero_facturas', 'total', 'estado', 'created_at', 'updated_at'];
            if (in_array($orderBy, $columnasValidas)) {
                $query->orderBy($orderBy, $orderDir);
            } else {
                // Si no es válido, usar el orden por defecto
                $query->orderBy('fecha', 'desc');
            }
        } catch (\Exception $e) {
            Log::error('FacturaFiltroService::aplicarFiltros - Error al aplicar filtros', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Si hay error, aplicar solo el orden por defecto
            $query->orderBy('fecha', 'desc');
        }

        return $query;
    }
}

