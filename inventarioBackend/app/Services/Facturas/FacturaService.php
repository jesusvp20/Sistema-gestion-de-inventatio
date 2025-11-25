<?php

namespace App\Services\Facturas;

use App\Models\facturaModel;
use App\Models\DetalleFacturaModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service principal para lógica de negocio de facturas
 * 
 * MODIFICADO: 2025-11-24
 * Cambio: Refactorizado para usar servicios auxiliares (FacturaFechaService, FacturaDetalleService, FacturaFiltroService)
 * Razón: Modularizar código y reducir complejidad
 */
class FacturaService
{
    protected $fechaService;
    protected $detalleService;
    protected $filtroService;

    public function __construct(
        FacturaFechaService $fechaService,
        FacturaDetalleService $detalleService,
        FacturaFiltroService $filtroService
    ) {
        $this->fechaService = $fechaService;
        $this->detalleService = $detalleService;
        $this->filtroService = $filtroService;
    }

    /**
     * Formatea fecha de factura
     */
    public function formatearFechaFactura($factura)
    {
        return $this->fechaService->formatearFechaFactura($factura);
    }

    /**
     * Aplica filtros a query de facturas
     */
    public function aplicarFiltros($query, $request)
    {
        return $this->filtroService->aplicarFiltros($query, $request);
    }

    /**
     * Crea una nueva factura con sus detalles
     */
    public function crearFactura($request)
    {
        try {
            Log::info('FacturaService::crearFactura - Iniciando creación de factura', [
                'cliente_id' => $request->cliente_id,
                'numero_facturas' => $request->numero_facturas,
                'detalles_count' => count($request->detalles ?? []),
            ]);

            DB::beginTransaction();

            // Generar número de factura único si no se proporciona
            $numeroFactura = $request->numero_facturas;
            if (empty($numeroFactura)) {
                $ultimaFactura = facturaModel::orderBy('id', 'desc')->first();
                
                if ($ultimaFactura && preg_match('/F-(\d+)/', $ultimaFactura->numero_facturas, $matches)) {
                    $numeroSiguiente = (int)$matches[1] + 1;
                } else {
                    $numeroSiguiente = 1;
                }
                
                $numeroFactura = 'F-' . str_pad($numeroSiguiente, 6, '0', STR_PAD_LEFT);
            }

            // Verificar que el número de factura no exista
            if ($request->has('numero_facturas') && !empty($request->numero_facturas)) {
                $existe = facturaModel::where('numero_facturas', $numeroFactura)->exists();
                if ($existe) {
                    $ultimaFactura = facturaModel::orderBy('id', 'desc')->first();
                    if ($ultimaFactura && preg_match('/F-(\d+)/', $ultimaFactura->numero_facturas, $matches)) {
                        $numeroSiguiente = (int)$matches[1] + 1;
                    } else {
                        $numeroSiguiente = 1;
                    }
                    $numeroFactura = 'F-' . str_pad($numeroSiguiente, 6, '0', STR_PAD_LEFT);
                }
            }

            // Crear la factura
            $factura = facturaModel::create([
                'numero_facturas' => $numeroFactura,
                'fecha' => Carbon::now(),
                'cliente_id' => $request->cliente_id,
                'proveedor_id' => $request->proveedor_id ?? null,
                'total' => 0,
                'estado' => 'pendiente',
            ]);

            $totalFactura = 0;

            // Crear los detalles usando el servicio de detalles
            foreach ($request->detalles as $detalle) {
                $detalleCreado = $this->detalleService->crearDetalle($factura, $detalle['producto_id'], $detalle['cantidad']);
                $totalFactura += $detalleCreado->subtotal;
            }

            // Actualizar el total de la factura
            $factura->total = $totalFactura;
            $factura->save();

            DB::commit();

            // Cargar relaciones para la respuesta
            $factura->load([
                'cliente:id,nombre,email,telefono',
                'proveedor:id,nombre,direccion,telefono',
                'detalles.producto:IdProducto,nombre,precio',
            ]);

            // Convertir a array y formatear fechas
            $facturaArray = $factura->toArray();
            
            // Formatear fechas manualmente
            if (isset($facturaArray['fecha'])) {
                try {
                    if ($factura->fecha instanceof Carbon) {
                        $facturaArray['fecha'] = $factura->fecha->format('d/m/Y');
                    } else {
                        $fechaRaw = $factura->getRawOriginal('fecha');
                        if ($fechaRaw instanceof Carbon) {
                            $facturaArray['fecha'] = $fechaRaw->format('d/m/Y');
                        } elseif (is_string($fechaRaw) && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaRaw)) {
                            $carbon = Carbon::parse($fechaRaw);
                            $facturaArray['fecha'] = $carbon->format('d/m/Y');
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('FacturaService::crearFactura - Error al formatear fecha', [
                        'exception' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Factura creada exitosamente',
                'data' => $facturaArray
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FacturaService::crearFactura - Error al crear factura', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la factura',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualiza los detalles de una factura
     */
    public function actualizarDetalles($factura, $detalles)
    {
        return $this->detalleService->actualizarDetalles($factura, $detalles);
    }
}
