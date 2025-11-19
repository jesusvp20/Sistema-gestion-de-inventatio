<?php

namespace App\Http\Controllers;

use App\Models\productosModel;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Validator;

class productosController extends Controller
{
    #[OA\Get(
        path: "/productos",
        tags: ["Productos"],
        summary: "Listar productos",
        description: "Obtiene una lista de todos los productos"
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de productos obtenida exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Producto")),
                new OA\Property(property: "statusCode", type: "integer", example: 200)
            ]
        )
    )]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function index()
    {
        try {
            $productos = productosModel::all();
            
            if ($productos->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No hay productos registrados en el inventario',
                    'data' => [],
                    'statusCode' => 200
                ], 200);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Productos obtenidos exitosamente',
                'data' => $productos,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al listar productos: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la lista de productos',
                'statusCode' => 500
            ], 500);
        }
    }

    #[OA\Post(
        path: "/productos",
        tags: ["Productos"],
        summary: "Crear producto",
        description: "Crea un nuevo producto en el inventario",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "precio", "cantidad_disponible", "estado"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Producto Ejemplo"),
                    new OA\Property(property: "descripcion", type: "string", example: "Descripción del producto", nullable: true),
                    new OA\Property(property: "precio", type: "number", format: "float", example: 99.99),
                    new OA\Property(property: "cantidad_disponible", type: "integer", example: 50),
                    new OA\Property(property: "categoria", type: "string", example: "Electrónica", nullable: true),
                    new OA\Property(property: "proveedor", type: "integer", nullable: true),
                    new OA\Property(property: "codigoProducto", type: "string", example: "PROD-001", nullable: true),
                    new OA\Property(property: "estado", type: "string", enum: ["disponible", "agotado", "expirado"], example: "disponible")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Producto creado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Producto creado exitosamente"),
                new OA\Property(property: "data", ref: "#/components/schemas/Producto"),
                new OA\Property(property: "statusCode", type: "integer", example: 201)
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'required|numeric|min:0',
                'cantidad_disponible' => 'required|integer|min:0',
                'categoria' => 'nullable|string|max:255',
                'proveedor' => 'nullable|integer',
                'codigoProducto' => 'nullable|string|max:255|unique:producto,codigoProducto',
                'estado' => 'required|string|in:disponible,agotado,expirado'
            ], [
                'nombre.required' => 'El nombre del producto es obligatorio',
                'nombre.string' => 'El nombre debe ser texto',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres',
                'precio.required' => 'El precio es obligatorio',
                'precio.numeric' => 'El precio debe ser un número válido',
                'precio.min' => 'El precio no puede ser negativo',
                'cantidad_disponible.required' => 'La cantidad disponible es obligatoria',
                'cantidad_disponible.integer' => 'La cantidad debe ser un número entero',
                'cantidad_disponible.min' => 'La cantidad no puede ser negativa',
                'categoria.string' => 'La categoría debe ser texto',
                'categoria.max' => 'La categoría no puede exceder 255 caracteres',
                'proveedor.integer' => 'El proveedor debe ser un número válido',
                'codigoProducto.string' => 'El código debe ser texto',
                'codigoProducto.max' => 'El código no puede exceder 255 caracteres',
                'codigoProducto.unique' => 'Este código de producto ya existe',
                'estado.required' => 'El estado es obligatorio',
                'estado.string' => 'El estado debe ser un texto',
                'estado.in' => 'El estado debe ser: disponible, agotado o expirado'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos. Por favor, revise los campos.',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            $producto = productosModel::create($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Producto creado exitosamente',
                'data' => $producto,
                'statusCode' => 201
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear producto: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    #[OA\Get(
        path: "/productos/{id}",
        tags: ["Productos"],
        summary: "Mostrar producto",
        description: "Obtiene la información de un producto específico",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(
        response: 200,
        description: "Producto obtenido exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", ref: "#/components/schemas/Producto")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function show($id)
    {
        try {
            $producto = productosModel::find($id);
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El producto con ID ' . $id . ' no fue encontrado',
                    'statusCode' => 404
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Producto obtenido exitosamente',
                'data' => $producto,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al mostrar producto: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el producto',
                'statusCode' => 500
            ], 500);
        }
    }

    #[OA\Put(
        path: "/productos/{id}",
        tags: ["Productos"],
        summary: "Actualizar producto",
        description: "Actualiza la información de un producto existente",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Producto Ejemplo"),
                    new OA\Property(property: "descripcion", type: "string", example: "Descripción del producto", nullable: true),
                    new OA\Property(property: "precio", type: "number", format: "float", example: 99.99),
                    new OA\Property(property: "cantidad_disponible", type: "integer", example: 50),
                    new OA\Property(property: "categoria", type: "string", example: "Electrónica", nullable: true),
                    new OA\Property(property: "proveedor", type: "integer", nullable: true),
                    new OA\Property(property: "codigoProducto", type: "string", example: "PROD-001", nullable: true),
                    new OA\Property(property: "estado", type: "string", enum: ["disponible", "agotado", "expirado"], example: "disponible")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Producto actualizado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Producto actualizado exitosamente"),
                new OA\Property(property: "data", ref: "#/components/schemas/Producto")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function update(Request $request, $id)
    {
        try {
            $producto = productosModel::find($id);
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El producto con ID ' . $id . ' no fue encontrado',
                    'statusCode' => 404
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'sometimes|numeric|min:0',
                'cantidad_disponible' => 'sometimes|integer|min:0',
                'categoria' => 'nullable|string|max:255',
                'codigoProducto' => 'nullable|string|max:255|unique:producto,codigoProducto,' . $id . ',IdProducto',
                'estado' => 'sometimes|string|in:disponible,agotado,expirado'
            ], [
                'nombre.max' => 'El nombre no puede exceder 255 caracteres',
                'precio.numeric' => 'El precio debe ser un número válido',
                'precio.min' => 'El precio no puede ser negativo',
                'cantidad_disponible.integer' => 'La cantidad debe ser un número entero',
                'cantidad_disponible.min' => 'La cantidad no puede ser negativa',
                'codigoProducto.unique' => 'Este código de producto ya existe',
                'estado.string' => 'El estado debe ser un texto',
                'estado.in' => 'El estado debe ser: disponible, agotado o expirado'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos. Por favor, revise los campos.',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }
            
            $producto->update($request->all());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Producto actualizado exitosamente',
                'data' => $producto->fresh(),
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar producto: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el producto',
                'statusCode' => 500
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/productos/{id}",
        tags: ["Productos"],
        summary: "Eliminar producto",
        description: "Elimina un producto del inventario",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(
        response: 200,
        description: "Producto eliminado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "El producto se ha eliminado correctamente")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function destroy($id)
    {
        try {
            $producto = productosModel::find($id);
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El producto con ID ' . $id . ' no fue encontrado',
                    'statusCode' => 404
                ], 404);
            }
            
            $nombreProducto = $producto->nombre;
            $producto->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'El producto "' . $nombreProducto . '" se ha eliminado correctamente',
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar producto: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el producto. Puede que esté relacionado con ventas o facturas.',
                'statusCode' => 500
            ], 500);
        }
    }

    #[OA\Patch(
        path: "/productos/{id}/cambiar-estado",
        tags: ["Productos"],
        summary: "Cambiar estado del producto",
        description: "Alterna el estado (activo/inactivo) de un producto",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(
        response: 200,
        description: "Estado del producto actualizado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", ref: "#/components/schemas/Producto")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function cambiarEstado($id)
    {
        $producto = productosModel::find($id);
        if (!$producto) {
            return response()->json(['status' => 'error', 'message' => 'Producto no encontrado'], 404);
        }
        // Alternar entre disponible y agotado
        if ($producto->estado === 'disponible') {
            $producto->estado = 'agotado';
        } elseif ($producto->estado === 'agotado') {
            $producto->estado = 'disponible';
        } else {
            // Si está expirado, cambiar a disponible
            $producto->estado = 'disponible';
        }
        $producto->save();
        return response()->json(['status' => 'success', 'data' => $producto]);
    }

    #[OA\Get(
        path: "/productos/buscar",
        tags: ["Productos"],
        summary: "Buscar productos por nombre",
        description: "Busca productos por nombre (búsqueda parcial)",
        parameters: [new OA\Parameter(name: "nombre", in: "query", required: true, schema: new OA\Schema(type: "string"))]
    )]
    #[OA\Response(
        response: 200,
        description: "Productos encontrados",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Producto"))
            ]
        )
    )]
    public function buscar(Request $request)
    {
        $nombre = $request->input('nombre');
        $productos = productosModel::where('nombre', 'like', "%{$nombre}%")->get();
        return response()->json(['status' => 'success', 'data' => $productos]);
    }

    #[OA\Get(
        path: "/productos/activos",
        tags: ["Productos"],
        summary: "Listar productos activos",
        description: "Obtiene una lista de todos los productos con estado activo"
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de productos activos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Producto"))
            ]
        )
    )]
    public function activos()
    {
        $productos = productosModel::where('estado', 'disponible')->get();
        return response()->json(['status' => 'success', 'data' => $productos]);
    }

    #[OA\Get(
        path: "/productos/ordenar",
        tags: ["Productos"],
        summary: "Ordenar productos por precio",
        description: "Obtiene productos ordenados por precio",
        parameters: [new OA\Parameter(
            name: "orden",
            in: "query",
            required: false,
            description: "Orden ascendente o descendente",
            schema: new OA\Schema(type: "string", enum: ["asc","desc"], default: "asc")
        )]
    )]
    #[OA\Response(
        response: 200,
        description: "Productos ordenados",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Producto"))
            ]
        )
    )]
    public function ordenar(Request $request)
    {
        $orden = $request->input('orden', 'asc');
        $productos = productosModel::orderBy('precio', $orden)->get();
        return response()->json(['status' => 'success', 'data' => $productos]);
    }
}