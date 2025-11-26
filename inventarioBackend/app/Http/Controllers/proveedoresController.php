<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\proveedorModel;
use OpenApi\Attributes as OA;

class proveedoresController extends Controller
{
    /**
     * Listar todos los proveedores
     */
    public function index()
    {
        try {
            $proveedores = proveedorModel::all();

            if ($proveedores->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay proveedores registrados',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $proveedores
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al listar proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un proveedor específico por ID
     * 
     * MODIFICADO: 2025-01-27
     * Cambio: Método show() verificado y documentación mejorada
     * Razón: Corregir error "Call to undefined method" al acceder a GET /api/proveedores/{id}
     */
    #[OA\Get(path: "/proveedores/{id}", tags: ["Proveedores"], summary: "Obtener proveedor por ID", parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))])]
    #[OA\Response(response: 200, description: "Proveedor encontrado")]
    #[OA\Response(response: 404, description: "Proveedor no encontrado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function show($id)
    {
        try {
            $proveedor = proveedorModel::find($id);
            
            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $proveedor
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error en proveedoresController::show', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el proveedor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    #[OA\Post(path: "/proveedores", tags: ["Proveedores"], summary: "Crear proveedor", requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["nombre", "direccion", "telefono", "estado"], properties: [
        new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
        new OA\Property(property: "direccion", type: "string", example: "Calle 123 #45-67"),
        new OA\Property(property: "telefono", type: "string", example: "3001234567"),
        new OA\Property(property: "estado", type: "boolean", example: true)
    ])))]
    #[OA\Response(response: 201, description: "Proveedor creado")]
    #[OA\Response(response: 400, description: "Datos inválidos")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'nombre'=> 'required|string|max:255',
                'direccion'=> 'required|string|max:200',
                'telefono'=> 'required|string|max:50',
                'estado'=>   'required|boolean'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $proveedor = proveedorModel::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor creado exitosamente',
                'data' => $proveedor
            ], 201);
     
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un proveedor existente
     * 
     * MODIFICADO: 2025-01-27
     * Cambio: Mejorado manejo de errores, validación y logging
     * Razón: Corregir error 500 al actualizar proveedores
     */
    #[OA\Put(path: "/proveedores/{id}", tags: ["Proveedores"], summary: "Actualizar proveedor", parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: "nombre", type: "string", example: "Proveedor XYZ"),
        new OA\Property(property: "direccion", type: "string", example: "Carrera 10 #20-30"),
        new OA\Property(property: "telefono", type: "string", example: "3101112233"),
        new OA\Property(property: "estado", type: "boolean", example: true)
    ])))]
    #[OA\Response(response: 200, description: "Proveedor actualizado")]
    #[OA\Response(response: 404, description: "Proveedor no encontrado")]
    #[OA\Response(response: 400, description: "Datos inválidos")]
    public function update(Request $request, $id)
    {
        try {
            $proveedor = proveedorModel::find($id);
            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no ha sido encontrado'
                ], 404);
            }

            // Validación mejorada - solo validar campos que se envían
            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:255',
                'direccion' => 'sometimes|string|max:200',
                'telefono' => 'sometimes|string|max:50',
                'estado' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Actualizar solo los campos que se envían
            $updateData = $request->only(['nombre', 'direccion', 'telefono', 'estado']);
            
            // Filtrar valores nulos
            $updateData = array_filter($updateData, function($value) {
                return $value !== null;
            });

            if (empty($updateData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se proporcionaron datos para actualizar'
                ], 400);
            }

            $proveedor->update($updateData);
            
            // Recargar el modelo para obtener los datos actualizados
            $proveedor->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor actualizado exitosamente',
                'data' => $proveedor
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error en proveedoresController::update', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el proveedor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar un proveedor del sistema
     * 
     * MODIFICADO: 2025-01-27
     * Cambio: Verificación de facturas asociadas antes de eliminar y mejor manejo de errores
     * Razón: Prevenir errores de violación de clave foránea y proporcionar mensajes claros
     */
    #[OA\Delete(path: "/proveedores/{id}", tags: ["Proveedores"], summary: "Eliminar proveedor", parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))])]
    #[OA\Response(response: 200, description: "Proveedor eliminado")]
    #[OA\Response(response: 404, description: "Proveedor no encontrado")]
    #[OA\Response(response: 409, description: "No se puede eliminar: tiene facturas asociadas")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function destroy($id)
    {
        try {
            $proveedor = proveedorModel::find($id);
            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado',
                ], 404);
            }

            // Verificar si hay facturas asociadas
            $facturasCount = \DB::table('facturas')
                ->where('proveedor_id', $id)
                ->count();

            if ($facturasCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar el proveedor porque tiene facturas asociadas',
                    'details' => [
                        'facturas_asociadas' => $facturasCount,
                        'sugerencia' => 'Elimine o actualice las facturas asociadas antes de eliminar el proveedor'
                    ]
                ], 409); // 409 Conflict - recurso en conflicto por dependencias
            }

            // Verificar si hay productos asociados (aunque no debería ser bloqueante)
            $productosCount = \DB::table('producto')
                ->where('proveedor', $id)
                ->count();

            // Intentar eliminar
            $proveedor->delete();

            $response = [
                'status' => 'success',
                'message' => 'El proveedor se ha eliminado correctamente'
            ];

            // Informar si había productos asociados (solo informativo)
            if ($productosCount > 0) {
                $response['warning'] = "Se encontraron {$productosCount} producto(s) asociado(s) que ahora no tienen proveedor asignado";
            }

            return response()->json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar específicamente errores de clave foránea
            if ($e->getCode() == '23503' || str_contains($e->getMessage(), 'Foreign key violation')) {
                \Log::warning('Intento de eliminar proveedor con dependencias', [
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar el proveedor porque tiene registros asociados',
                    'details' => [
                        'sugerencia' => 'Elimine o actualice los registros asociados (facturas, productos) antes de eliminar el proveedor'
                    ]
                ], 409);
            }

            \Log::error('Error en proveedoresController::destroy - QueryException', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el proveedor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error en proveedoresController::destroy', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el proveedor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Cambiar el estado de un proveedor (activo/inactivo)
     * 
     * MODIFICADO: 2025-01-27
     * Cambio: Mejorado manejo de errores y logging
     * Razón: Corregir error 500 al cambiar estado del proveedor
     */
    #[OA\Patch(path: "/proveedores/{id}/estado", tags: ["Proveedores"], summary: "Cambiar estado del proveedor", parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))])]
    #[OA\Response(response: 200, description: "Estado actualizado")]
    #[OA\Response(response: 404, description: "Proveedor no encontrado")]
    public function cambiarEstado($id)
    {
        try {
            $proveedor = proveedorModel::find($id);
            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            // Alternar el estado (convertir a boolean explícitamente)
            $nuevoEstado = !(bool)$proveedor->estado;
            $proveedor->estado = $nuevoEstado;
            
            if (!$proveedor->save()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo guardar el cambio de estado'
                ], 500);
            }

            // Recargar el modelo para obtener el estado actualizado
            $proveedor->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Estado del proveedor ha sido actualizado',
                'data' => $proveedor
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error en proveedoresController::cambiarEstado', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar el estado del proveedor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    #[OA\Get(path: "/proveedores/buscar", tags: ["Proveedores"], summary: "Buscar proveedores por nombre", parameters: [new OA\Parameter(name: "nombre", in: "query", required: true, schema: new OA\Schema(type: "string"))])]
    #[OA\Response(response: 200, description: "Resultados de búsqueda")]
    #[OA\Response(response: 400, description: "Parámetros inválidos")]
    #[OA\Response(response: 404, description: "Sin resultados")]
   public function buscarPorNombre(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El parámetro nombre es requerido',
                    'errors' => $validator->errors()
                ], 400);
            }

            $nombre = $request->input('nombre');
            $proveedores = proveedorModel::where('nombre', 'like', '%' . $nombre . '%')->get();

            if ($proveedores->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontraron proveedores con ese nombre',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $proveedores,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(path: "/proveedores/activos", tags: ["Proveedores"], summary: "Listar proveedores activos")]
    #[OA\Response(response: 200, description: "Lista de proveedores activos")]
    #[OA\Response(response: 404, description: "No hay proveedores activos")]
    public function listarActivos()
    {
        try {
            $proveedores = proveedorModel::where('estado', true)->get();

            if ($proveedores->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'No hay proveedores activos'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $proveedores], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al listar proveedores activos', 'error' => $e->getMessage()], 500);
        }
    }

}