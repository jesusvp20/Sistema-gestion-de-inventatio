<?php

namespace App\Http\Controllers;

use App\Models\facturaModel;
use App\Models\ProductosModel;
use App\Services\Facturas\FacturaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Controlador para operaciones CRUD de facturas
 * MODIFICADO: 2025-11-19
 * Cambio: Extraído de DetalleFacturaController para modularizar
 */
class facturaController extends Controller
{
    protected $facturaService;

    public function __construct(FacturaService $facturaService)
    {
        $this->facturaService = $facturaService;
    }

    /**
     * Listar todas las facturas con sus detalles completos
     * 
     * MODIFICADO: 2025-11-24
     * Cambio: Filtros ahora son completamente opcionales y manejan errores de forma segura
     * Razón: Los filtros causaban error 500 cuando el formato era incorrecto
     * Solución: Los filtros se aplican solo si están presentes y son válidos
     * 
     * MODIFICADO: 2025-11-24 - Corregido uso de IdProducto en lugar de id para productos
     * 
     * Parámetros opcionales:
     * - fecha_inicio: Fecha inicio (formato d/m/Y o Y-m-d)
     * - fecha_fin: Fecha fin (formato d/m/Y o Y-m-d)
     * - cliente: Nombre del cliente (búsqueda parcial)
     * - estado: Estado de la factura
     * - order_by: Campo para ordenar (fecha, id, numero_facturas, total, estado)
     * - order_dir: Dirección de orden (asc, desc)
     * - per_page: Cantidad de resultados por página (default: 15)
     */
    public function listar(Request $request)
    {
        try {
            // MODIFICADO: 2025-11-24 - Corregido carga de relaciones
            // Razón: No se pueden especificar columnas en relaciones anidadas cuando se necesita acceder a otra relación
            // Solución: Cargar producto sin restricción de columnas para permitir carga de proveedor
        $query = facturaModel::with([
            'cliente:id,nombre,email,telefono',
            'proveedor:id,nombre,direccion,telefono',
            'detalles' => function ($q) {
                $q->select('id', 'factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal');
                },
                'detalles.producto' => function ($q) {
                    $q->select('IdProducto', 'nombre', 'precio', 'descripcion', 'proveedor');
                },
                'detalles.producto.proveedor:id,nombre,empresa'
            ]);

            // Aplicar filtros opcionales (maneja errores internamente)
            $query = $this->facturaService->aplicarFiltros($query, $request);
            
            // Paginación con valor por defecto seguro
            $perPage = $request->get('per_page', 15);
            if (!is_numeric($perPage) || $perPage < 1 || $perPage > 100) {
                $perPage = 15;
            }
            
            $facturas = $query->paginate((int)$perPage);

        // MODIFICADO: 2025-11-25 - NO modificar modelo antes de toArray()
        // Cambio: Eliminado transform() que formateaba fechas antes de toArray()
        // Razón: Cuando se modifica el modelo antes de toArray(), Laravel intenta reparsear
        //        las fechas con el cast 'datetime', causando error con formato d/m/Y
        // Solución: Convertir a array primero, luego formatear fechas en el array
        
        $facturasArray = $facturas->toArray();
        $this->formatearFechasEnArray($facturasArray);
            $this->eliminarPdfPath($facturasArray); // MODIFICADO: 2025-11-24 - Eliminar pdf_path (no se usa, PDF se genera en Angular)

        return response()->json(['status' => 'success', 'data' => $facturasArray]);
        } catch (\Exception $e) {
            \Log::error('facturaController::listar - Error al listar facturas', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al listar las facturas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Mostrar una factura específica con todos sus detalles
     * 
     * MODIFICADO: 2025-11-24 - Corregido uso de IdProducto en lugar de id para productos
     * Razón: La clave primaria de ProductosModel es IdProducto, no id
     */
    public function mostrar($id)
    {
        try {
            // MODIFICADO: 2025-11-24 - Corregido: eliminada columna 'direccion' que no existe en clientes
            // MODIFICADO: 2025-11-24 - Corregido carga de relaciones para productos y proveedores
        $factura = facturaModel::with([
                'cliente:id,nombre,email,telefono',
                'proveedor:id,nombre,direccion,telefono',
            'detalles' => function ($q) {
                $q->select('id', 'factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal');
                },
                'detalles.producto' => function ($q) {
                    $q->select('IdProducto', 'nombre', 'precio', 'descripcion', 'proveedor');
                },
                'detalles.producto.proveedor:id,nombre,empresa,telefono'
        ])->find($id);

        if (!$factura) {
            return response()->json(['status' => 'error', 'message' => 'Factura no encontrada'], 404);
        }

        // MODIFICADO: 2025-11-25 - NO modificar modelo antes de toArray()
        // Cambio: Eliminado formatearFechaFactura() antes de toArray()
        // Razón: Cuando se modifica el modelo antes de toArray(), Laravel intenta reparsear
        //        las fechas con el cast 'datetime', causando error con formato d/m/Y
        // Solución: Convertir a array primero, luego formatear fechas en el array
        
        $facturaArray = $factura->toArray();
        $this->formatearFechasEnArray($facturaArray);
            $this->eliminarPdfPath($facturaArray); // MODIFICADO: 2025-11-24 - Eliminar pdf_path (no se usa, PDF se genera en Angular)
        
        return response()->json(['status' => 'success', 'data' => $facturaArray]);
        } catch (\Exception $e) {
            \Log::error('facturaController::mostrar - Error al mostrar factura', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la factura',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crear una nueva factura
     * 
     * MODIFICADO: 2025-11-24
     * Cambio: Agregado manejo de errores y validación mejorada
     * Razón: Mejorar la experiencia del usuario con mensajes de error más claros
     */
    public function store(Request $request)
    {
        try {
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'proveedor_id' => 'sometimes|integer|exists:proveedores,id',
            'numero_facturas' => 'sometimes|string|max:255',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|integer|exists:producto,IdProducto',
            'detalles.*.cantidad' => 'required|integer|min:1',
            ], [
                'cliente_id.required' => 'El ID del cliente es requerido',
                'cliente_id.exists' => 'El cliente especificado no existe',
                'detalles.required' => 'Debe incluir al menos un detalle de factura',
                'detalles.min' => 'Debe incluir al menos un detalle de factura',
                'detalles.*.producto_id.required' => 'El ID del producto es requerido en cada detalle',
                'detalles.*.producto_id.exists' => 'Uno o más productos no existen',
                'detalles.*.cantidad.required' => 'La cantidad es requerida en cada detalle',
                'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
        ]);

        return $this->facturaService->crearFactura($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('facturaController::store - Error al crear factura', [
                'exception' => $e->getMessage(),
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
     * Actualizar una factura existente
     * 
     * MODIFICADO: 2025-11-24
     * Cambio: Agregada actualización de detalles y soporte para abreviaciones de estado
     * Razón: Permitir actualizar detalles junto con la factura y aceptar abreviaciones (d/D, p/P, a/A)
     */
    public function actualizar(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $factura = facturaModel::find($id);

            if (!$factura) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Factura no encontrada',
                    'statusCode' => 404
                ], 404);
            }

            // Normalizar estado a minúsculas (acepta cualquier combinación de mayúsculas/minúsculas)
            $requestData = $request->all();
            if (isset($requestData['estado'])) {
                $estado = strtolower(trim($requestData['estado']));
                $estadosValidos = ['pendiente', 'disponible', 'agotado'];
                if (in_array($estado, $estadosValidos)) {
                    $requestData['estado'] = $estado;
                } else {
                    // Si no es válido, mantener el original para que la validación falle
                    $requestData['estado'] = $request->estado;
                }
            }

            $validator = Validator::make($requestData, [
                'cliente_id' => 'sometimes|integer|exists:clientes,id',
                'numero_facturas' => [
                    'sometimes',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($factura, $requestData) {
                        // Solo validar si el número de factura realmente está cambiando
                        if (isset($requestData['numero_facturas']) && $requestData['numero_facturas'] !== $factura->numero_facturas) {
                            // Verificar que el número de factura no exista en otra factura
                            $existe = facturaModel::where('numero_facturas', $value)
                                ->where('id', '!=', $factura->id)
                                ->exists();
                            if ($existe) {
                                $fail('El número de factura ya está en uso por otra factura.');
                            }
                        }
                    },
                ],
                'fecha' => 'sometimes|date',
                'estado' => 'sometimes|string|in:pendiente,disponible,agotado',
                'detalles' => 'sometimes|array|min:1',
                'detalles.*.id' => 'sometimes|integer|exists:detallefactura,id',
                'detalles.*.producto_id' => 'sometimes|integer|exists:producto,IdProducto',
                'detalles.*.cantidad' => 'sometimes|integer|min:1',
            ], [
                'cliente_id.exists' => 'El cliente especificado no existe',
                'fecha.date' => 'La fecha debe ser una fecha válida',
                'estado.in' => 'El estado debe ser: pendiente, disponible o agotado (acepta mayúsculas y minúsculas)',
                'detalles.*.producto_id.exists' => 'Uno o más productos no existen',
                'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            // Actualizar datos de la factura
            // MODIFICADO: 2025-11-24 - Solo actualizar campos que realmente cambiaron
            // Razón: Evitar problemas con validación de número de factura único cuando no se cambia
            if (isset($requestData['cliente_id'])) {
                $factura->cliente_id = $requestData['cliente_id'];
            }
            if (isset($requestData['proveedor_id'])) {
                $factura->proveedor_id = $requestData['proveedor_id'];
            }
            // Solo actualizar número de factura si realmente cambió
            if (isset($requestData['numero_facturas']) && $requestData['numero_facturas'] !== $factura->numero_facturas) {
                $factura->numero_facturas = $requestData['numero_facturas'];
            }
            if (isset($requestData['fecha'])) {
                $factura->fecha = $requestData['fecha'];
            }
            if (isset($requestData['estado'])) {
                $factura->estado = $requestData['estado'];
            }
            $factura->save();

            // Actualizar detalles si se proporcionan
            if ($request->has('detalles')) {
                $this->facturaService->actualizarDetalles($factura, $request->detalles);
            }

            DB::commit();

            // Cargar relaciones para la respuesta
            $facturaActualizada = $factura->load([
                'cliente:id,nombre,email,telefono',
                'proveedor:id,nombre,direccion,telefono',
                'detalles' => function ($q) {
                    $q->select('id', 'factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal');
                },
                'detalles.producto' => function ($q) {
                    $q->select('IdProducto', 'nombre', 'precio', 'descripcion', 'proveedor');
                },
                'detalles.producto.proveedor:id,nombre,empresa'
            ]);

            // MODIFICADO: 2025-11-25 - NO modificar modelo antes de toArray()
            $facturaArray = $facturaActualizada->toArray();
            $this->formatearFechasEnArray($facturaArray);
            $this->eliminarPdfPath($facturaArray); // MODIFICADO: 2025-11-24 - Eliminar pdf_path (no se usa, PDF se genera en Angular)

            return response()->json([
                'status' => 'success',
                'message' => 'Factura actualizada exitosamente',
                'data' => $facturaArray,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('facturaController::actualizar - Error al actualizar factura', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la factura',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Eliminar factura
     */
    public function eliminar($id)
    {
        try {
            $factura = facturaModel::with('detalles')->find($id);

            if (!$factura) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Factura no encontrada',
                    'statusCode' => 404
                ], 404);
            }

            DB::beginTransaction();

            // Restaurar stock de productos
            foreach ($factura->detalles as $detalle) {
                $producto = ProductosModel::find($detalle->producto_id);
                if ($producto) {
                    $producto->cantidad_disponible += $detalle->cantidad;
                    $producto->save();
                }
            }

            // Eliminar detalles y factura
            $factura->detalles()->delete();
            $factura->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Factura eliminada exitosamente',
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la factura',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
                'statusCode' => 500
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
     * Elimina el campo pdf_path de las respuestas
     * 
     * MODIFICADO: 2025-11-24
     * Cambio: Método agregado para eliminar pdf_path de las respuestas
     * Razón: El PDF se genera en Angular, no en el backend, por lo que este campo no se necesita
     * 
     * @param array &$data Array de datos (se modifica por referencia)
     * @return void
     */
    protected function eliminarPdfPath(&$data)
    {
        if (is_array($data)) {
            // Si es un array de paginación, limpiar dentro de 'data'
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as &$item) {
                    if (isset($item['pdf_path'])) {
                        unset($item['pdf_path']);
                    }
                }
            } else {
                // Si es un objeto simple, eliminar directamente
                if (isset($data['pdf_path'])) {
                    unset($data['pdf_path']);
                }
            }
        }
    }
}
