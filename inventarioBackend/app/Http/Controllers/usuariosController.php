<?php

namespace App\Http\Controllers;

use App\Models\UsuariosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class usuariosController extends Controller
{
    /**
     * Registrar nuevo usuario
     */
    #[OA\Post(
        path: "/register",
        summary: "Registrar usuario",
        description: "Crea un nuevo usuario en el sistema",
        tags: ["Usuarios"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "correo", type: "string", format: "email", example: "juan@ejemplo.com"),
                    new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
                    new OA\Property(property: "contraseña", type: "string", example: "password123"),
                    new OA\Property(property: "tipo", type: "string", enum: ["admin", "usuario", "vendedor"], example: "usuario")
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Usuario creado exitosamente")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'correo' => 'required|string|email|max:255|unique:usuarios,correo',
                'nombre' => 'required|string|max:255',
                'contraseña' => 'required|string|min:6',
                'tipo' => 'required|string|in:admin,usuario,vendedor'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            $usuario = UsuariosModel::create([
                'correo' => $request->correo,
                'nombre' => $request->nombre,
                'contraseña' => Hash::make($request->contraseña),
                'tipo' => $request->tipo,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario creado exitosamente',
                'data' => $usuario,
                'statusCode' => 201
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear usuario: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Iniciar sesión
     */
    #[OA\Post(
        path: "/login",
        summary: "Iniciar sesión",
        description: "Autentica un usuario y devuelve un token de acceso",
        tags: ["Usuarios"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
                    new OA\Property(property: "password", type: "string", example: "password123")
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: "Inicio de sesión exitoso")]
    #[OA\Response(response: 401, description: "Credenciales incorrectas")]
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'password' => 'required|string|min:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            $usuario = UsuariosModel::where('nombre', $request->nombre)->first();

            if (!$usuario || !Hash::check($request->password, $usuario->contraseña)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales incorrectas',
                    'statusCode' => 401
                ], 401);
            }

            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $token
                ],
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al iniciar sesión: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Obtener usuario autenticado
     */
    #[OA\Get(
        path: "/user",
        summary: "Obtener usuario autenticado",
        tags: ["Usuarios"]
    )]
    public function user(Request $request)
    {
        try {
            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado',
                    'statusCode' => 401
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'data' => $usuario,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener usuario: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Cerrar sesión
     */
    #[OA\Post(
        path: "/logout",
        summary: "Cerrar sesión",
        tags: ["Usuarios"]
    )]
    public function logout(Request $request)
    {
        try {
            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado',
                    'statusCode' => 401
                ], 401);
            }

            $usuario->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada exitosamente',
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar sesión: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        try {
            $usuarios = UsuariosModel::all();
            
            if ($usuarios->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontraron usuarios',
                    'statusCode' => 404
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $usuarios,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al listar usuarios: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        try {
            $usuario = UsuariosModel::find($id);

            if (!$usuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado',
                    'statusCode' => 404
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $usuario,
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener usuario: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Crear nuevo usuario (requiere autenticación)
     */
    public function store(Request $request)
    {
        return $this->register($request);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        try {
            $usuario = UsuariosModel::find($id);

            if (!$usuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado',
                    'statusCode' => 404
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'correo' => 'sometimes|string|email|max:255|unique:usuarios,correo,' . $id,
                'nombre' => 'sometimes|string|max:255',
                'contraseña' => 'sometimes|string|min:6',
                'tipo' => 'sometimes|string|in:admin,usuario,vendedor'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            $updateData = [];
            
            if ($request->filled('correo')) {
                $updateData['correo'] = $request->correo;
            }
            
            if ($request->filled('nombre')) {
                $updateData['nombre'] = $request->nombre;
            }
            
            if ($request->filled('contraseña')) {
                $updateData['contraseña'] = Hash::make($request->contraseña);
            }
            
            if ($request->filled('tipo')) {
                $updateData['tipo'] = $request->tipo;
            }

            $usuario->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario actualizado exitosamente',
                'data' => $usuario->fresh(),
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar usuario: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Eliminar usuario
     */
    public function destroy($id)
    {
        try {
            $usuario = UsuariosModel::find($id);

            if (!$usuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado',
                    'statusCode' => 404
                ], 404);
            }

            $usuario->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario eliminado exitosamente',
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar usuario: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }
}

