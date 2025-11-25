<?php

namespace App\Services;

use App\Models\productosModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service para lógica de negocio de productos
 * MODIFICADO: 2025-11-19
 * Cambio: Extraído de productosController para modularizar código
 */
class ProductoService
{
    /**
     * Formatea fechas de producto
     * 
     * MODIFICADO: 2025-11-20
     * Cambio: Validación de formato antes de parsear para evitar errores con fechas ya formateadas
     * Razón: getRawOriginal() puede devolver fechas en formato d/m/Y que Carbon::parse() no puede parsear
     */
    public function formatearFechasProducto($producto)
    {
        // Formatear fecha_creacion
        if ($producto->fecha_creacion) {
            try {
                // Si ya es una instancia de Carbon, formatear directamente
                if ($producto->fecha_creacion instanceof Carbon) {
                    $producto->setAttribute('fecha_creacion', $producto->fecha_creacion->format('d/m/Y'));
                } 
                // Si es un string, verificar si ya está formateado
                elseif (is_string($producto->fecha_creacion)) {
                    // Verificar si ya está en formato d/m/Y
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $producto->fecha_creacion)) {
                        // Ya está formateado, no hacer nada
                    } else {
                        // Intentar parsear desde formato ISO o timestamp
                        $fechaCreacionRaw = $producto->getRawOriginal('fecha_creacion');
                        if ($fechaCreacionRaw) {
                            // Verificar si el raw también está en formato d/m/Y
                            if (is_string($fechaCreacionRaw) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaCreacionRaw)) {
                                $producto->setAttribute('fecha_creacion', $fechaCreacionRaw);
                            } else {
                                $fechaParseada = $this->parsearFechaSegura($fechaCreacionRaw);
                                if ($fechaParseada) {
                                    $producto->setAttribute('fecha_creacion', $fechaParseada->format('d/m/Y'));
                                }
                            }
                        }
                    }
                } 
                // Si no está formateado, obtener el valor raw
                else {
                    $fechaCreacionRaw = $producto->getRawOriginal('fecha_creacion');
                    if ($fechaCreacionRaw) {
                        // Verificar si el raw está en formato d/m/Y
                        if (is_string($fechaCreacionRaw) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaCreacionRaw)) {
                            $producto->setAttribute('fecha_creacion', $fechaCreacionRaw);
                        } elseif ($fechaCreacionRaw instanceof Carbon) {
                            $producto->setAttribute('fecha_creacion', $fechaCreacionRaw->format('d/m/Y'));
                        } else {
                            $fechaParseada = $this->parsearFechaSegura($fechaCreacionRaw);
                            if ($fechaParseada) {
                                $producto->setAttribute('fecha_creacion', $fechaParseada->format('d/m/Y'));
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Si falla, intentar con el atributo directamente si es Carbon
                if ($producto->fecha_creacion instanceof Carbon) {
                    $producto->setAttribute('fecha_creacion', $producto->fecha_creacion->format('d/m/Y'));
                }
            }
        }
        
        // Formatear fecha_actualizacion
        if ($producto->fecha_actualizacion) {
            try {
                // Si ya es una instancia de Carbon, formatear directamente
                if ($producto->fecha_actualizacion instanceof Carbon) {
                    $producto->setAttribute('fecha_actualizacion', $producto->fecha_actualizacion->format('d/m/Y'));
                } 
                // Si es un string, verificar si ya está formateado
                elseif (is_string($producto->fecha_actualizacion)) {
                    // Verificar si ya está en formato d/m/Y
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $producto->fecha_actualizacion)) {
                        // Ya está formateado, no hacer nada
                    } else {
                        // Intentar parsear desde formato ISO o timestamp
                        $fechaActualizacionRaw = $producto->getRawOriginal('fecha_actualizacion');
                        if ($fechaActualizacionRaw) {
                            // Verificar si el raw también está en formato d/m/Y
                            if (is_string($fechaActualizacionRaw) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaActualizacionRaw)) {
                                $producto->setAttribute('fecha_actualizacion', $fechaActualizacionRaw);
                            } else {
                                $fechaParseada = $this->parsearFechaSegura($fechaActualizacionRaw);
                                if ($fechaParseada) {
                                    $producto->setAttribute('fecha_actualizacion', $fechaParseada->format('d/m/Y'));
                                }
                            }
                        }
                    }
                } 
                // Si no está formateado, obtener el valor raw
                else {
                    $fechaActualizacionRaw = $producto->getRawOriginal('fecha_actualizacion');
                    if ($fechaActualizacionRaw) {
                        // Verificar si el raw está en formato d/m/Y
                        if (is_string($fechaActualizacionRaw) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaActualizacionRaw)) {
                            $producto->setAttribute('fecha_actualizacion', $fechaActualizacionRaw);
                        } elseif ($fechaActualizacionRaw instanceof Carbon) {
                            $producto->setAttribute('fecha_actualizacion', $fechaActualizacionRaw->format('d/m/Y'));
                        } else {
                            $fechaParseada = $this->parsearFechaSegura($fechaActualizacionRaw);
                            if ($fechaParseada) {
                                $producto->setAttribute('fecha_actualizacion', $fechaParseada->format('d/m/Y'));
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Si falla, intentar con el atributo directamente si es Carbon
                if ($producto->fecha_actualizacion instanceof Carbon) {
                    $producto->setAttribute('fecha_actualizacion', $producto->fecha_actualizacion->format('d/m/Y'));
                }
            }
        }
        
        return $producto;
    }

    /**
     * Parsear fecha de forma segura, manejando múltiples formatos
     * 
     * MODIFICADO: 2025-11-20
     * Cambio: Método helper para parsear fechas evitando errores con formato d/m/Y
     * Razón: Carbon::parse() no puede parsear fechas en formato d/m/Y, necesitamos intentar múltiples formatos
     */
    private function parsearFechaSegura($valor)
    {
        // Si es null o vacío, retornar null
        if (empty($valor)) {
            return null;
        }
        
        // Si ya es Carbon, retornarlo
        if ($valor instanceof Carbon) {
            return $valor;
        }
        
        // Convertir a string si no lo es
        $valorString = is_string($valor) ? $valor : (string) $valor;
        
        // Si ya está en formato d/m/Y, parsearlo con createFromFormat
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valorString)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $valorString);
            } catch (\Exception $e) {
                Log::warning('ProductoService::parsearFechaSegura - error al parsear formato d/m/Y', [
                    'valor' => $valorString,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
        
        // Si está en formato ISO (YYYY-MM-DD), parsearlo
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valorString)) {
            try {
                return Carbon::parse($valorString);
            } catch (\Exception $e) {
                Log::warning('ProductoService::parsearFechaSegura - error al parsear formato ISO', [
                    'valor' => $valorString,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
        
        // Intentar parsear con Carbon::parse() como último recurso
        try {
            return Carbon::parse($valorString);
        } catch (\Exception $e) {
            Log::warning('ProductoService::parsearFechaSegura - error al parsear fecha', [
                'valor' => $valorString,
                'tipo' => gettype($valor),
                'exception' => $e->getMessage(),
            ]);
            // Si todo falla, retornar null
            return null;
        }
    }
}

