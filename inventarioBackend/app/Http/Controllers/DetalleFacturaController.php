<?php

namespace App\Http\Controllers;

use App\Models\facturaModel;
use App\Models\DetalleFacturaModel;
use App\Models\ProductosModel;
use App\Models\ClientesModel;
use App\Http\Controllers\facturaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class DetalleFacturaController extends Controller
{
    protected $facturaController;

    public function __construct(facturaController $facturaController)
    {
        $this->facturaController = $facturaController;
    }

    /**
     * Listar todas las facturas con sus detalles completos
     */
    #[OA\Get(
        path: "/facturas",
        tags: ["Facturas"],
        summary: "Listar facturas con detalles",
        parameters: [
            new OA\Parameter(name: "fecha_inicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "fecha_fin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "cliente", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "estado", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "order_by", in: "query", required: false, schema: new OA\Schema(type: "string", default: "fecha")),
            new OA\Parameter(name: "order_dir", in: "query", required: false, schema: new OA\Schema(type: "string", default: "desc")),
            new OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
        ]
    )]
    #[OA\Response(response: 200, description: "Lista paginada de facturas")]
    public function listarFacturas(Request $request)
    {
        $query = facturaModel::with([
            'cliente:id,nombre,email,telefono',
            'detalles.producto.proveedor:id,nombre,empresa',
            'detalles' => function ($q) {
                $q->select('id', 'factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal');
            }
        ]);

        // Filtros
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        if ($request->has('cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->cliente}%");
            });
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento
        $orderBy = $request->get('order_by', 'fecha');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $facturas = $query->paginate($request->get('per_page', 15));

        // Agregar información calculada
        $facturas->getCollection()->transform(function ($factura) {
            $factura->fecha_formateada = Carbon::parse($factura->fecha)->format('d/m/Y H:i:s');
            $factura->total_items = $factura->detalles->count();
            $factura->total_productos = $factura->detalles->sum('cantidad');
            return $factura;
        });

        return response()->json(['status' => 'success', 'data' => $facturas]);
    }

    /**
     * Mostrar una factura específica con todos sus detalles
     */
    #[OA\Get(
        path: "/facturas/{id}",
        tags: ["Facturas"],
        summary: "Mostrar factura con detalles",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(response: 200, description: "Factura encontrada")]
    #[OA\Response(response: 404, description: "Factura no encontrada")]
    public function mostrarFactura($id)
    {
        $factura = facturaModel::with([
            'cliente:id,nombre,email,telefono,direccion',
            'detalles.producto.proveedor:id,nombre,empresa,telefono',
            'detalles' => function ($q) {
                $q->select('id', 'factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal');
            }
        ])->find($id);

        if (!$factura) {
            return response()->json(['status' => 'error', 'message' => 'Factura no encontrada'], 404);
        }

        // Agregar información calculada
        $factura->fecha_formateada = Carbon::parse($factura->fecha)->format('d/m/Y H:i:s');
        $factura->total_items = $factura->detalles->count();
        $factura->total_productos = $factura->detalles->sum('cantidad');

        return response()->json(['status' => 'success', 'data' => $factura]);
    }

    /**
     * Crear una nueva factura con sus detalles
     */
    #[OA\Post(
        path: "/facturas",
        tags: ["Facturas"],
        summary: "Crear factura con detalles",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["cliente_id", "detalles"],
                properties: [
                    new OA\Property(property: "cliente_id", type: "integer", example: 1),
                    new OA\Property(property: "numero_facturas", type: "string", example: "F-000123", nullable: true),
                    new OA\Property(property: "detalles", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "producto_id", type: "integer", example: 1),
                        new OA\Property(property: "cantidad", type: "integer", example: 2)
                    ]))
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Factura creada")]
    public function crearFactura(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'numero_facturas' => 'sometimes|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|integer|exists:producto,IdProducto',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        // Usar el facturaController para crear la factura
        return $this->facturaController->store($request);
    }

    /**
     * Agregar un detalle a una factura existente
     * Payload: factura_id, producto_id, cantidad
     */
    #[OA\Post(
        path: "/facturas/{id}/detalles",
        tags: ["Facturas"],
        summary: "Agregar detalle a factura",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["factura_id", "producto_id", "cantidad"],
                properties: [
                    new OA\Property(property: "factura_id", type: "integer", example: 1),
                    new OA\Property(property: "producto_id", type: "integer", example: 1),
                    new OA\Property(property: "cantidad", type: "integer", example: 2)
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Detalle agregado")]
    public function agregarDetalle(Request $request)
    {
        $request->validate([
            'factura_id' => 'required|integer|exists:facturas,id',
            'producto_id' => 'required|integer|exists:producto,IdProducto',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $factura = facturaModel::find($request->factura_id);
            $producto = ProductosModel::find($request->producto_id);

            if (!$producto || $producto->cantidad_disponible < $request->cantidad) {
                throw new \Exception('Stock no disponible');
            }

            $precio = $producto->precio;
            $subtotal = $precio * $request->cantidad;

            $detalle = DetalleFacturaModel::create([
                'factura_id' => $factura->id,
                'producto_id' => $producto->IdProducto,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $subtotal,
            ]);

            // Actualizar stock y total de factura
            $producto->cantidad_disponible -= $request->cantidad;
            $producto->save();

            $factura->total = ($factura->total ?? 0) + $subtotal;
            $factura->save();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Detalle agregado', 'data' => $detalle->load('producto')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar un detalle (p. ej. cambiar cantidad)
     */
    #[OA\Put(
        path: "/facturas/detalles/{id}",
        tags: ["Facturas"],
        summary: "Actualizar detalle de factura",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: "cantidad", type: "integer", example: 3)
        ]))
    )]
    #[OA\Response(response: 200, description: "Detalle actualizado")]
    #[OA\Response(response: 404, description: "Detalle no encontrado")]
    public function actualizarDetalle($id, Request $request)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $detalle = DetalleFacturaModel::find($id);
            if (!$detalle) {
                return response()->json(['status' => 'error', 'message' => 'Detalle no encontrado'], 404);
            }

            $producto = ProductosModel::find($detalle->producto_id);
            $factura = facturaModel::find($detalle->factura_id);

            $nuevaCantidad = $request->cantidad;
            $diferencia = $nuevaCantidad - $detalle->cantidad;

            if ($diferencia > 0 && $producto->cantidad_disponible < $diferencia) {
                throw new \Exception('Stock insuficiente para aumentar la cantidad');
            }

            // Ajustar stock
            $producto->cantidad_disponible -= $diferencia;
            $producto->save();

            // Actualizar detalle
            $detalle->cantidad = $nuevaCantidad;
            $detalle->subtotal = $detalle->precio_unitario * $nuevaCantidad;
            $detalle->save();

            // Recalcular total de la factura para evitar inconsistencias
            $factura->total = $factura->detalles()->sum('subtotal');
            $factura->save();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Detalle actualizado', 'data' => $detalle->load('producto')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un detalle de factura y restaurar stock
     */
    #[OA\Delete(
        path: "/facturas/detalles/{id}",
        tags: ["Facturas"],
        summary: "Eliminar detalle de factura",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(response: 200, description: "Detalle eliminado")]
    #[OA\Response(response: 404, description: "Detalle no encontrado")]
    public function eliminarDetalle($id)
    {
        DB::beginTransaction();
        try {
            $detalle = DetalleFacturaModel::find($id);
            if (!$detalle) {
                return response()->json(['status' => 'error', 'message' => 'Detalle no encontrado'], 404);
            }

            $producto = ProductosModel::find($detalle->producto_id);
            $factura = facturaModel::find($detalle->factura_id);

            // Restaurar stock
            if ($producto) {
                $producto->cantidad_disponible += $detalle->cantidad;
                $producto->save();
            }

            // Eliminar detalle
            $detalle->delete();

            // Recalcular total
            if ($factura) {
                $factura->total = $factura->detalles()->sum('subtotal');
                $factura->save();
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Detalle eliminado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Fin de DetalleFacturaController: solo operaciones de detalle

    /**
     * Listar productos disponibles (paginado, con búsqueda)
     */
    #[OA\Get(
        path: "/facturas/productos/listar",
        tags: ["Facturas"],
        summary: "Listar productos para facturación",
        parameters: [
            new OA\Parameter(name: "q", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
        ]
    )]
    #[OA\Response(response: 200, description: "Lista de productos")]
    public function listarProductos(Request $request)
    {
        $query = ProductosModel::query();

        if ($request->has('q')) {
            $q = $request->q;
            $query->where('nombre', 'like', "%{$q}%")
                  ->orWhere('descripcion', 'like', "%{$q}%")
                  ->orWhere('codigoProducto', 'like', "%{$q}%");
        }

        $perPage = $request->get('per_page', 15);
        $productos = $query->select(['IdProducto', 'nombre', 'precio', 'cantidad_disponible', 'codigoProducto'])->paginate($perPage);

        return response()->json(['status' => 'success', 'data' => $productos]);
    }

    /**
     * Mostrar un producto específico
     */
    #[OA\Get(
        path: "/facturas/productos/{id}",
        tags: ["Facturas"],
        summary: "Mostrar producto",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(response: 200, description: "Producto encontrado")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function mostrarProducto($id)
    {
        $producto = ProductosModel::find($id);
        if (!$producto) {
            return response()->json(['status' => 'error', 'message' => 'Producto no encontrado'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $producto]);
    }

    /**
     * Listar clientes (útil para seleccionar cliente desde Angular)
     */
    #[OA\Get(
        path: "/facturas/clientes/listar",
        tags: ["Facturas"],
        summary: "Listar clientes para facturación",
        parameters: [
            new OA\Parameter(name: "q", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
        ]
    )]
    #[OA\Response(response: 200, description: "Lista de clientes")]
    public function listarClientes(Request $request)
    {
        $query = ClientesModel::query();

        if ($request->has('q')) {
            $q = $request->q;
            $query->where('nombre', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
        }

        $clientes = $query->select(['id', 'nombre', 'email', 'telefono'])->paginate($request->get('per_page', 15));

        return response()->json(['status' => 'success', 'data' => $clientes]);
    }

    /**
     * Generar un número de factura sugerido (no guarda nada)
     */
    #[OA\Get(
        path: "/facturas/proximo-numero",
        tags: ["Facturas"],
        summary: "Siguiente número de factura"
    )]
    #[OA\Response(response: 200, description: "Número sugerido devuelto")]
    public function proximoNumero()
    {
        $lastId = facturaModel::max('id');
        $next = $lastId ? $lastId + 1 : 1;
        $numero = 'F-' . str_pad($next, 6, '0', STR_PAD_LEFT);

        return response()->json(['status' => 'success', 'data' => ['numero_factura' => $numero]]);
    }

    /**
     * Validar detalles enviados por el frontend y calcular totales (sin guardar)
     * Payload: detalles: [{producto_id, cantidad}]
     */
    #[OA\Post(
        path: "/facturas/validar-detalles",
        tags: ["Facturas"],
        summary: "Validar detalles y calcular totales (sin guardar)",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["detalles"],
                properties: [
                    new OA\Property(property: "detalles", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "producto_id", type: "integer", example: 1),
                        new OA\Property(property: "cantidad", type: "integer", example: 2)
                    ]))
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: "Detalles validados")]
    public function validarDetalles(Request $request)
    {
        $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|integer|exists:producto,IdProducto',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        $detalles = $request->detalles;
        $total = 0;
        $items = [];
        $errores = [];

        foreach ($detalles as $d) {
            $producto = ProductosModel::find($d['producto_id']);
            if (!$producto) {
                $errores[] = ['producto_id' => $d['producto_id'], 'message' => 'Producto no encontrado'];
                continue;
            }

            if ($producto->cantidad_disponible < $d['cantidad']) {
                $errores[] = ['producto_id' => $d['producto_id'], 'message' => 'Stock insuficiente', 'disponible' => $producto->cantidad_disponible];
            }

            $subtotal = $producto->precio * $d['cantidad'];
            $total += $subtotal;

            $items[] = [
                'producto_id' => $producto->IdProducto,
                'nombre' => $producto->nombre,
                'cantidad' => $d['cantidad'],
                'precio_unitario' => $producto->precio,
                'subtotal' => $subtotal,
                'disponible' => $producto->cantidad_disponible,
            ];
        }

        return response()->json(['status' => 'success', 'data' => ['items' => $items, 'total' => $total, 'errores' => $errores]]);
    }
}
