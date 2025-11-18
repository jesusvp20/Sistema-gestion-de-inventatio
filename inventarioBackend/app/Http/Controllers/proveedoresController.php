<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\proveedorModel;
use OpenApi\Attributes as OA;

class proveedoresController extends Controller
{
    #[OA\Get(path: "/proveedores", tags: ["Proveedores"], summary: "Listar proveedores", description: "Obtiene todos los proveedores registrados")]
    #[OA\Response(response: 200, description: "Lista de proveedores", content: new OA\JsonContent(properties: [
        new OA\Property(property: "status", type: "string", example: "success"),
        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Proveedor"))
    ]))]
    #[OA\Response(response: 404, description: "No hay proveedores")]
    #[OA\Response(response: 500, description: "Error del servidor")]
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

    #[OA\Put(path: "/proveedores/{id}", tags: ["Proveedores"], summary: "Actualizar proveedor", parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: "nombre", type: "string", example: "Proveedor XYZ"),
        new OA\Property(property: "direccion", type: "string", example: "Carrera 10 #20-30"),
        new OA\Property(property: "telefono", type: "string", example: "3101112233"),
        new OA\Property(property: "estado", type: "boolean", example: true)
    ])))]
    #[OA\Response(response: 200, description: "Proveedor actualizado")]
    #[OA\Response(response: 404, description: "Proveedor no encontrado")]
    #[OA\Response(response: 400, description: "Datos inválidos")]
   public function update(Request $request, $id){
      try{
            $proveedor = proveedorModel::find($id);
            if (!$proveedor){
                return response()->json([
                'status'=> 'error',
                'message'=> 'Proveedor no ha sido encontrado'
                ],404);
            }

            $validator = Validator::make($request->all(),[
                'nombre'=> [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('proveedores')->ignore($proveedor->id),
                ],
                'direccion'=> 'sometimes|string|max:200',
                'telefono'=>  'sometimes|string|max:50',
                'estado'=>   'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $proveedor->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor actualizado exitosamente',
                'data' => $proveedor
            ], 200);
      }catch(\Exception $e){
     return response()->json([
        'status'=> 'error',
         'message' => $e->getMessage() 
     ], 500);
   }

    }

    #[OA\Delete(path: "/proveedores/{id}", tags: ["Proveedores"], summary: "Eliminar proveedor", parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))])]
    #[OA\Response(response: 200, description: "Proveedor eliminado")]
    #[OA\Response(response: 404, description: "Proveedor no encontrado")]
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
            $proveedor->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'El proveedor se ha eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   #[OA\Patch(path: "/proveedores/{id}/estado", tags: ["Proveedores"], summary: "Cambiar estado del proveedor", parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))])]
   #[OA\Response(response: 200, description: "Estado actualizado")]
   #[OA\Response(response: 404, description: "Proveedor no encontrado")]
   public function cambiarEstado($id){
        try {
            $proveedor = proveedorModel::find($id);
            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            $proveedor->estado = !$proveedor->estado;
            $proveedor->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Estado del proveedor ha sido actualizado',
                'data' => $proveedor
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar el estado del proveedor',
                'error' => $e->getMessage()
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