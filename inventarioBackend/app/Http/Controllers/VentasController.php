<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ventasModel;
use App\Models\ProductosModel;
use App\Services\Ventas\VentaService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class VentasController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    /**
     * Lista todas las ventas con su cliente y detalles de productos.
     */
    public function index(Request $request){
        try{
            // MODIFICADO: 2025-11-23 01:45
            // Cambio: Validación para aceptar formato d/m/Y (formato usuario) además de Y-m-d
            // Razón: El usuario quiere usar formato dd/mm/aaaa en los filtros
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        // Aceptar formato d/m/Y o Y-m-d
                        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value) && 
                            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            $fail('El campo fecha_inicio debe tener el formato dd/mm/aaaa o aaaa-mm-dd.');
                        }
                    },
                ],
                'fecha_fin' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        // Aceptar formato d/m/Y o Y-m-d
                        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value) && 
                            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            $fail('El campo fecha_fin debe tener el formato dd/mm/aaaa o aaaa-mm-dd.');
                            return;
                        }
                        
                        // Validar que fecha_fin sea mayor o igual que fecha_inicio
                        if ($request->has('fecha_inicio')) {
                            $fechaInicio = $this->convertirFechaParaBD($request->input('fecha_inicio'));
                            $fechaFin = $this->convertirFechaParaBD($value);
                            
                            if ($fechaInicio && $fechaFin && $fechaFin < $fechaInicio) {
                                $fail('El campo fecha_fin debe ser mayor o igual que fecha_inicio.');
                            }
                        }
                    },
                ],
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
    
            // MODIFICADO: 2025-11-23 01:45
            // Cambio: Convertir fechas de d/m/Y a Y-m-d antes de usar en consultas
            // Razón: Las consultas de BD necesitan formato Y-m-d
            if ($request->has('fecha_inicio')) {
                $fechaInicio = $this->convertirFechaParaBD($request->input('fecha_inicio'));
                if ($fechaInicio) {
                    $query->whereDate('fecha_venta', '>=', $fechaInicio);
                }
            }
    
            if ($request->has('fecha_fin')) {
                $fechaFin = $this->convertirFechaParaBD($request->input('fecha_fin'));
                if ($fechaFin) {
                    $query->whereDate('fecha_venta', '<=', $fechaFin);
                }
            }
    
            $porPagina = $request->input('por_pagina', 15);
            $ventas = $query->orderBy('fecha_venta', 'desc')->paginate($porPagina);
    
            if($ventas->isEmpty()){
                return response()->json([
                    'status'=> 'error',
                    'message' => 'No hay ventas registradas que coincidan con los filtros.',
                ], 404);
            }

            // MODIFICADO: 2025-11-23 01:15
            // Cambio: Convertir a array PRIMERO, luego formatear fechas en el array
            // Razón: Evitar que Laravel intente reparsear fechas ya formateadas cuando serializa el modelo
            // Problema anterior: setAttribute() modificaba el modelo, y toArray() intentaba aplicar el cast datetime de nuevo
            // DEBUG: 2025-11-23 02:00 - Agregar logs para identificar dónde falla el parseo
            \Log::debug('VentasController::index - Antes de toArray()', [
                'total_ventas' => $ventas->count(),
                'primer_venta_id' => $ventas->first() ? $ventas->first()->id_ventas : null,
            ]);
            
            // Verificar el valor de fecha_venta antes de serializar
            if ($ventas->first()) {
                $primerVenta = $ventas->first();
                \Log::debug('VentasController::index - Inspección de primera venta antes de toArray()', [
                    'id_venta' => $primerVenta->id_ventas,
                    'fecha_venta_atributo' => $primerVenta->fecha_venta,
                    'tipo_fecha_venta' => gettype($primerVenta->fecha_venta),
                    'es_carbon' => $primerVenta->fecha_venta instanceof \Carbon\Carbon,
                    'fecha_venta_raw' => $primerVenta->getRawOriginal('fecha_venta'),
                    'tipo_raw' => gettype($primerVenta->getRawOriginal('fecha_venta')),
                ]);
            }
            
            try {
                $ventasArray = $ventas->toArray();
                \Log::debug('VentasController::index - Después de toArray()', [
                    'primer_fecha_venta' => $ventasArray['data'][0]['fecha_venta'] ?? 'N/A',
                    'tipo_fecha' => isset($ventasArray['data'][0]['fecha_venta']) ? gettype($ventasArray['data'][0]['fecha_venta']) : 'N/A',
                ]);
            } catch (\Exception $e) {
                \Log::error('VentasController::index - Error en toArray()', [
                    'error' => $e->getMessage(),
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
            
            try {
                \Log::debug('VentasController::index - Antes de formatearFechasEnArray()');
                $this->formatearFechasEnArray($ventasArray);
                \Log::debug('VentasController::index - Después de formatearFechasEnArray()');
            } catch (\Exception $e) {
                \Log::error('VentasController::index - Error en formatearFechasEnArray()', [
                    'error' => $e->getMessage(),
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            return response()->json([
                'status'=> 'success',
                'data' => $ventasArray
            ], 200);

        }catch(\Exception $e){
            // MODIFICADO: 2025-11-20
            // Cambio: Mensaje de error personalizado para verificación de fechas
            // Razón: Verificar si el error de parseo de fechas persiste o se solucionó
            
            \Log::error('Error al listar ventas', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Verificar si el error está relacionado con fechas - DETECCIÓN MEJORADA
            $mensajeError = $e->getMessage();
            $esErrorFecha = stripos($mensajeError, 'parse') !== false || 
                           stripos($mensajeError, 'date') !== false || 
                           stripos($mensajeError, 'fecha') !== false ||
                           stripos($mensajeError, 'time') !== false ||
                           stripos($mensajeError, '19/11/2025') !== false ||
                           stripos($mensajeError, 'Could not parse') !== false ||
                           preg_match('/\d{2}\/\d{2}\/\d{4}/', $mensajeError);
            
            // SIEMPRE mostrar mensaje personalizado si es error de fechas
            if ($esErrorFecha) {
                return response()->json([
                    'status'=> 'error',
                    'message' => 'No se ha podido encontrar las ventas. Error en el formato de fechas.',
                    'error_original' => $e->getMessage(),
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'detalle' => 'El sistema no pudo procesar las fechas correctamente. Verifique el formato de las fechas en la base de datos.',
                    'trace' => config('app.debug') ? explode("\n", $e->getTraceAsString()) : 'Oculto en producción',
                    'verificacion' => 'Este mensaje confirma que el código nuevo se está ejecutando'
                ], 500);
            }
            
            return response()->json([
                'status'=> 'error',
                'message' => 'Error al listar ventas',
                'error' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace_completo' => $e->getTraceAsString()
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
        ], [
            'id_cliente.required' => 'El ID del cliente es obligatorio',
            'id_cliente.integer' => 'El ID del cliente debe ser un número entero',
            'id_cliente.exists' => 'El cliente con el ID especificado no existe',
            'detalles.required' => 'Los detalles de la venta son obligatorios',
            'detalles.array' => 'Los detalles deben ser un arreglo',
            'detalles.min' => 'Debe incluir al menos un producto en los detalles',
            'detalles.*.id_producto.required' => 'El ID del producto es obligatorio en cada detalle',
            'detalles.*.id_producto.integer' => 'El ID del producto debe ser un número entero',
            'detalles.*.id_producto.exists' => 'El producto con el ID especificado no existe',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada detalle',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos. Por favor, revise los campos.',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $venta = DB::transaction(function () use ($request) {
                $procesado = $this->ventaService->procesarDetalles($request->detalles);

                $venta = ventasModel::create([
                    'id_cliente' => $request->id_cliente,
                    'total' => $procesado['total'],
                ]);

                $venta->detalles()->createMany($procesado['detalles']);
                $venta = $venta->load('cliente', 'detalles.producto');
                
                return $venta; // MODIFICADO: 2025-11-25 - NO formatear antes de toArray()
            });

            // MODIFICADO: 2025-11-25 - NO modificar modelo antes de toArray()
            // Cambio: Eliminado formatearFechasVenta() antes de toArray()
            // Razón: Cuando se modifica el modelo antes de toArray(), Laravel intenta reparsear
            //        las fechas con el cast 'datetime', causando error con formato d/m/Y
            // Solución: Convertir a array primero, luego formatear fechas en el array
            
            $ventaArray = $venta->toArray();
            $this->formatearFechasEnArray($ventaArray);
            
            return response()->json(['status' => 'success', 'message' => 'Venta creada exitosamente', 'data' => $ventaArray], 201);

        } catch (\Exception $e) {
            // MODIFICADO: 2025-11-20 - Mensaje de error detallado
            \Log::error('Error al crear venta', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'status' => 'error', 
                'message' => 'Error al crear la venta', 
                'error' => $e->getMessage(),
                'detalles' => config('app.debug') ? [
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString()),
                ] : null
            ], 500);
        }
    }

    /**
     * Muestra una venta específica con todos sus detalles.
     */
    #[OA\Get(
        path: "/ventas/{id}",
        summary: "Mostrar venta",
        description: "Obtiene la información de una venta específica con todos sus detalles",
        tags: ["Ventas"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID de la venta", schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Venta obtenida exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Venta no encontrada")]
    public function show($id)
    {
        try {
            $venta = ventasModel::with('cliente', 'detalles.producto')->find($id);

            if (!$venta) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Venta no encontrada',
                    'statusCode' => 404
                ], 404);
            }

            // MODIFICADO: 2025-11-25 - NO modificar modelo antes de toArray()
            // Cambio: Eliminado formatearFechasVenta() antes de toArray()
            // Razón: Cuando se modifica el modelo antes de toArray(), Laravel intenta reparsear
            //        las fechas con el cast 'datetime', causando error con formato d/m/Y
            // Solución: Convertir a array primero, luego formatear fechas en el array
            
            $ventaArray = $venta->toArray();
            $this->formatearFechasEnArray($ventaArray);

            return response()->json([
                'status' => 'success',
                'message' => 'Venta obtenida exitosamente',
                'data' => $ventaArray,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al obtener venta', [
                'venta_id' => $id,
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la venta',
                'error' => $e->getMessage(),
                'detalles' => config('app.debug') ? [
                    'venta_id' => $id,
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString()),
                ] : null,
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Actualiza una venta existente.
     * Permite actualizar el cliente y/o los productos de la venta.
     * Si actualizas los productos, se reemplazan TODOS los detalles anteriores.
     * 
     * MODIFICADO: 2025-11-19
     * Cambio: Mejorada validación y mensajes de error para hacer el JSON más intuitivo
     * Razón: El usuario necesita entender mejor qué campos puede enviar y cómo funciona la actualización
     */
    public function update(Request $request, $id)
    {
        // Validar que al menos se envíe un campo para actualizar
        if (!$request->has('id_cliente') && !$request->has('detalles')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes enviar al menos un campo para actualizar: "id_cliente" o "detalles"',
                'ejemplo' => [
                    'id_cliente' => 1,
                    'detalles' => [
                        ['id_producto' => 1, 'cantidad' => 4]
                    ]
                ]
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'id_cliente' => 'sometimes|integer|exists:clientes,id',
            'detalles' => 'sometimes|array|min:1',
            'detalles.*.id_producto' => 'required_with:detalles|integer|exists:producto,IdProducto',
            'detalles.*.cantidad' => 'required_with:detalles|integer|min:1',
        ], [
            'id_cliente.integer' => 'El ID del cliente debe ser un número entero',
            'id_cliente.exists' => 'El cliente con el ID especificado no existe',
            'detalles.array' => 'Los detalles deben ser un arreglo de productos',
            'detalles.min' => 'Si envías detalles, debes incluir al menos un producto',
            'detalles.*.id_producto.required' => 'Cada producto debe tener un "id_producto"',
            'detalles.*.id_producto.integer' => 'El ID del producto debe ser un número entero',
            'detalles.*.id_producto.exists' => 'El producto con el ID especificado no existe',
            'detalles.*.cantidad.required' => 'Cada producto debe tener una "cantidad"',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos. Por favor, revise los campos.',
                'errors' => $validator->errors(),
                'ejemplo_valido' => [
                    'id_cliente' => 1,
                    'detalles' => [
                        ['id_producto' => 1, 'cantidad' => 4],
                        ['id_producto' => 2, 'cantidad' => 2]
                    ]
                ]
            ], 400);
        }

        try {
            $ventaActualizada = DB::transaction(function () use ($request, $id) {
                $venta = ventasModel::with('detalles.producto')->findOrFail($id);

                $totalVenta = $venta->total; // Mantener el total actual si no se cambian los detalles

                // Si se envían nuevos detalles, reemplazar todos los anteriores
                if ($request->has('detalles')) {
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

                    // 3. Procesar los nuevos detalles usando el service
                    $procesado = $this->ventaService->procesarDetalles($request->detalles);
                    $totalVenta = $procesado['total'];
                    $nuevosDetallesData = $procesado['detalles'];

                    // 4. Crear los nuevos detalles
                    if (!empty($nuevosDetallesData)) {
                        $venta->detalles()->createMany($nuevosDetallesData);
                    }
                }

                // 5. Actualizar el cliente si se envió
                $venta->update([
                    'id_cliente' => $request->input('id_cliente', $venta->id_cliente),
                    'total' => $totalVenta,
                ]);

                $venta = $venta->load('cliente', 'detalles.producto');
                return $venta; // MODIFICADO: 2025-11-25 - NO formatear antes de toArray()
            });

            // MODIFICADO: 2025-11-25 - NO modificar modelo antes de toArray()
            // Cambio: Eliminado formatearFechasVenta() antes de toArray()
            // Razón: Cuando se modifica el modelo antes de toArray(), Laravel intenta reparsear
            //        las fechas con el cast 'datetime', causando error con formato d/m/Y
            // Solución: Convertir a array primero, luego formatear fechas en el array
            
            $ventaArray = $ventaActualizada->toArray();
            $this->formatearFechasEnArray($ventaArray);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Venta actualizada exitosamente',
                'data' => $ventaArray
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Venta no encontrada',
                'id_buscado' => $id
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar venta', [
                'venta_id' => $id,
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la venta',
                'error' => $e->getMessage(),
                'detalles' => config('app.debug') ? [
                    'venta_id' => $id,
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString()),
                ] : null
            ], 500);
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
            \Log::error('Error al eliminar venta', [
                'venta_id' => $id,
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la venta',
                'error' => $e->getMessage(),
                'detalles' => config('app.debug') ? [
                    'venta_id' => $id,
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    /**
     * Muestra el historial de ventas con detalles.
     */
    public function historial(Request $request)
    {
        try {
            // MODIFICADO: 2025-11-23 01:50
            // Cambio: Validación para aceptar formato d/m/Y (formato usuario) además de Y-m-d
            // Razón: El usuario quiere usar formato dd/mm/aaaa en los filtros
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        // Aceptar formato d/m/Y o Y-m-d
                        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value) && 
                            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            $fail('El campo fecha_inicio debe tener el formato dd/mm/aaaa o aaaa-mm-dd.');
                        }
                    },
                ],
                'fecha_fin' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        // Aceptar formato d/m/Y o Y-m-d
                        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value) && 
                            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            $fail('El campo fecha_fin debe tener el formato dd/mm/aaaa o aaaa-mm-dd.');
                            return;
                        }
                        
                        // Validar que fecha_fin sea mayor o igual que fecha_inicio
                        if ($request->has('fecha_inicio')) {
                            $fechaInicio = $this->convertirFechaParaBD($request->input('fecha_inicio'));
                            $fechaFin = $this->convertirFechaParaBD($value);
                            
                            if ($fechaInicio && $fechaFin && $fechaFin < $fechaInicio) {
                                $fail('El campo fecha_fin debe ser mayor o igual que fecha_inicio.');
                            }
                        }
                    },
                ],
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

            // MODIFICADO: 2025-11-23 01:50
            // Cambio: Convertir fechas de d/m/Y a Y-m-d antes de usar en consultas
            // Razón: Las consultas de BD necesitan formato Y-m-d
            if ($request->has('fecha_inicio')) {
                $fechaInicio = $this->convertirFechaParaBD($request->input('fecha_inicio'));
                if ($fechaInicio) {
                    $query->whereDate('fecha_venta', '>=', $fechaInicio);
                }
            }

            if ($request->has('fecha_fin')) {
                $fechaFin = $this->convertirFechaParaBD($request->input('fecha_fin'));
                if ($fechaFin) {
                    $query->whereDate('fecha_venta', '<=', $fechaFin);
                }
            }

            $porPagina = $request->input('por_pagina', 15);
            $ventas = $query->orderBy('fecha_venta', 'desc')->paginate($porPagina);

            if ($ventas->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay ventas registradas que coincidan con los filtros.',
                ], 404);
            }

            // MODIFICADO: 2025-11-23 01:25
            // Cambio: Convertir a array PRIMERO, luego formatear fechas en el array
            // Razón: Evitar que Laravel intente reparsear fechas ya formateadas cuando serializa el modelo
            $ventasArray = $ventas->toArray();
            $this->formatearFechasEnArray($ventasArray);

            return response()->json([
                'status' => 'success',
                'data' => $ventasArray
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener historial de ventas', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el historial de ventas',
                'error' => $e->getMessage(),
                'detalles' => config('app.debug') ? [
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString()),
                ] : null
            ], 500);
        }
    }

    /**
     * Formatea fechas en array usando Carbon directamente
     */
    protected function formatearFechasEnArray(&$data)
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    $this->formatearFechasEnArray($value);
                } elseif (in_array($key, ['fecha', 'fecha_creacion', 'fecha_actualizacion', 'fecha_venta']) && $value) {
                    try {
                        $value = Carbon::parse($value)->format('d/m/Y');
                    } catch (\Exception $e) {
                        // Si falla, dejar el valor original
                    }
                }
            }
        }
    }

    /**
     * Convierte fecha a formato BD usando Carbon directamente
     */
    private function convertirFechaParaBD($fecha)
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

