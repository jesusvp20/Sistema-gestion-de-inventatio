<?php

namespace App\Http\Controllers;

use App\Models\productosModel;
use App\Services\ProductoService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class productosController extends Controller
{
    protected $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

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
            
            // MODIFICADO: 2025-11-19 - Formatear fechas
            $productosArray = $productos->toArray();
            $this->formatearFechasEnArray($productosArray);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Productos obtenidos exitosamente',
                'data' => $productosArray,
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

    public function store(Request $request)
    {
        try {
            // Convertir estado a string si viene como boolean
            $requestData = $request->all();
            if (isset($requestData['estado']) && is_bool($requestData['estado'])) {
                $requestData['estado'] = $requestData['estado'] ? 'disponible' : 'agotado';
            }
            if (isset($requestData['estado']) && !is_string($requestData['estado'])) {
                $requestData['estado'] = (string) $requestData['estado'];
            }
            
            $validator = Validator::make($requestData, [
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
                'estado.string' => 'El estado debe ser un texto (disponible, agotado o expirado). No se acepta true/false.',
                'estado.in' => 'El estado debe ser uno de estos valores: disponible, agotado o expirado'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos. Por favor, revise los campos.',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            $producto = productosModel::create($requestData);
            
            // MODIFICADO: 2025-11-19 - Formatear fechas
            $producto = $this->productoService->formatearFechasProducto($producto);
            $productoArray = $producto->toArray();
            $this->formatearFechasEnArray($productoArray);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Producto creado exitosamente',
                'data' => $productoArray,
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
            
            // MODIFICADO: 2025-11-19 - Formatear fechas
            $producto = $this->productoService->formatearFechasProducto($producto);
            $productoArray = $producto->toArray();
            $this->formatearFechasEnArray($productoArray);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Producto obtenido exitosamente',
                'data' => $productoArray,
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
            
            // Convertir estado a string si viene como boolean
            $requestData = $request->all();
            if (isset($requestData['estado']) && is_bool($requestData['estado'])) {
                $requestData['estado'] = $requestData['estado'] ? 'disponible' : 'agotado';
            }
            if (isset($requestData['estado']) && !is_string($requestData['estado'])) {
                $requestData['estado'] = (string) $requestData['estado'];
            }
            
            $validator = Validator::make($requestData, [
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
                'estado.string' => 'El estado debe ser un texto (disponible, agotado o expirado). No se acepta true/false.',
                'estado.in' => 'El estado debe ser uno de estos valores: disponible, agotado o expirado'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos. Por favor, revise los campos.',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }
            
            $producto->update($requestData);
            
            // MODIFICADO: 2025-11-19 - Formatear fechas
            $producto = $producto->fresh();
            $producto = $this->productoService->formatearFechasProducto($producto);
            $productoArray = $producto->toArray();
            $this->formatearFechasEnArray($productoArray);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Producto actualizado exitosamente',
                'data' => $productoArray,
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
        
        // MODIFICADO: 2025-11-19 - Formatear fechas
        $producto = $this->productoService->formatearFechasProducto($producto);
        $productoArray = $producto->toArray();
        $this->formatearFechasEnArray($productoArray);
        
        return response()->json(['status' => 'success', 'data' => $productoArray]);
    }

    public function buscar(Request $request)
    {
        $nombre = $request->input('nombre');
        $productos = productosModel::where('nombre', 'like', "%{$nombre}%")->get();
        
        // MODIFICADO: 2025-11-19 - Formatear fechas
        $productosArray = $productos->toArray();
        $this->formatearFechasEnArray($productosArray);
        
        return response()->json(['status' => 'success', 'data' => $productosArray]);
    }

    public function disponibles()
    {
        try {
            $productos = productosModel::where('estado', 'disponible')->get();
            
            if ($productos->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No hay productos disponibles en el inventario',
                    'data' => [],
                    'statusCode' => 200
                ], 200);
            }
            
            // MODIFICADO: 2025-11-19 - Formatear fechas
            $productosArray = $productos->toArray();
            $this->formatearFechasEnArray($productosArray);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Productos disponibles obtenidos exitosamente',
                'data' => $productosArray,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al listar productos activos: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener productos disponibles',
                'statusCode' => 500
            ], 500);
        }
    }

    public function ordenar(Request $request)
    {
        $orden = $request->input('orden', 'asc');
        $productos = productosModel::orderBy('precio', $orden)->get();
        
        // MODIFICADO: 2025-11-19 - Formatear fechas
        $productosArray = $productos->toArray();
        $this->formatearFechasEnArray($productosArray);
        
        return response()->json(['status' => 'success', 'data' => $productosArray]);
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
}