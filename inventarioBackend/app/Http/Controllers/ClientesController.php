<?php

namespace App\Http\Controllers;

use App\Models\ClientesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ClientesController extends Controller
{
    #[OA\Get(
        path: "/clientes",
        summary: "Listar todos los clientes",
        description: "Obtiene una lista de todos los clientes registrados en el sistema",
        tags: ["Clientes"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de clientes obtenida exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Cliente"))
            ]
        )
    )]
    #[OA\Response(response: 404, description: "No se encontraron clientes")]
    public function index()
    {
        $clientes = ClientesModel::all();
        if ($clientes->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay clientes registrados',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $clientes
        ], 200);
    }

    #[OA\Post(
        path: "/clientes",
        summary: "Crear nuevo cliente",
        description: "Crea un nuevo cliente en el sistema",
        tags: ["Clientes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "email", "identificacion", "telefono", "estado"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "María García"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "maria@ejemplo.com"),
                    new OA\Property(property: "identificacion", type: "string", example: "123456789"),
                    new OA\Property(property: "telefono", type: "string", example: "+1234567890"),
                    new OA\Property(property: "estado", type: "boolean", example: true)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Cliente creado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Cliente creado exitosamente"),
                new OA\Property(property: "data", ref: "#/components/schemas/Cliente")
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Datos inválidos")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:clientes',
                'identificacion' => 'required|string|max:50|unique:clientes',
                'telefono' => 'required|string|max:100',
                'estado' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $cliente = ClientesModel::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente creado exitosamente',
                'data' => $cliente
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/clientes/{id}",
        summary: "Actualizar cliente",
        description: "Actualiza la información de un cliente existente",
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID del cliente", schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "María García"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "maria@ejemplo.com"),
                    new OA\Property(property: "identificacion", type: "string", example: "123456789"),
                    new OA\Property(property: "telefono", type: "string", example: "+1234567890"),
                    new OA\Property(property: "estado", type: "boolean", example: true)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Cliente actualizado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Cliente actualizado exitosamente"),
                new OA\Property(property: "data", ref: "#/components/schemas/Cliente")
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Datos inválidos")]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    public function update(Request $request, $id)
    {
        try {
            $cliente = ClientesModel::find($id);

            if (!$cliente) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('clientes')->ignore($cliente->id),
                ],
                'identificacion' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('clientes')->ignore($cliente->id),
                ],
                'telefono' => 'sometimes|string|max:100',
                'estado' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos invalidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $cliente->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente actualizado exitosamente',
                'data' => $cliente
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    #[OA\Delete(
        path: "/clientes/{id}",
        summary: "Eliminar cliente",
        description: "Elimina un cliente del sistema",
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID del cliente", schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Cliente eliminado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "El cliente se ha eliminado correctamente")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function destroy($id)
    {
        try {
            $cliente = ClientesModel::find($id);

            if (!$cliente) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $cliente->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'El cliente se ha eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    #[OA\Patch(
        path: "/clientes/{id}/estado",
        summary: "Cambiar estado del cliente",
        description: "Alterna el estado (activo/inactivo) de un cliente",
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID del cliente", schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Estado del cliente actualizado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Estado del cliente ha sido actualizado"),
                new OA\Property(property: "data", ref: "#/components/schemas/Cliente")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    public function cambiarEstado($id)
    {
        try {
            $cliente = ClientesModel::find($id);

            if (!$cliente) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            // Alternar el estado
            $cliente->estado = !$cliente->estado;
            $cliente->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Estado del cliente ha sido actualizado',
                'data' => $cliente
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar el estado del cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/clientes/buscar",
        summary: "Buscar cliente por nombre",
        description: "Busca clientes por nombre (búsqueda parcial)",
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "nombre", in: "query", required: true, description: "Nombre a buscar", schema: new OA\Schema(type: "string"))
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Clientes encontrados",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Cliente"))
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Parámetro nombre requerido")]
    #[OA\Response(response: 404, description: "No se encontraron clientes")]
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
            $clientes = ClientesModel::where('nombre', 'like', '%' . $nombre . '%')->get();

            if ($clientes->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontraron clientes con ese nombre',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $clientes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    #[OA\Get(
        path: "/clientes/activos",
        summary: "Listar clientes activos",
        description: "Obtiene una lista de todos los clientes activos",
        tags: ["Clientes"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de clientes activos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Cliente"))
            ]
        )
    )]
    #[OA\Response(response: 404, description: "No hay clientes activos")]
    public function listarActivos()
    {
        try {
            $clientes = ClientesModel::where('estado', true)->get();

            if ($clientes->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay clientes activos registrados',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $clientes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al listar clientes activos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}