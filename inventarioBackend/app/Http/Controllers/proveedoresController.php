<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\proveedorModel;

class proveedoresController extends Controller
{
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