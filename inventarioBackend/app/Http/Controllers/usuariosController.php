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
     * 
     * MODIFICADO: 2025-11-18 21:00:00
     * Cambio: Mejorado manejo de errores y seguridad
     * Razón: Prevenir información sensible en respuestas de error
     */
    #[OA\Post(
        path: "/login",
        summary: "Iniciar sesión",
        description: "Autentica un usuario y devuelve un token de acceso Bearer para usar en endpoints protegidos",
        tags: ["Usuarios"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "correo", type: "string", format: "email", example: "admin@sistema.com"),
                    new OA\Property(property: "password", type: "string", example: "Admin2024!")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200, 
        description: "Inicio de sesión exitoso",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Inicio de sesión exitoso"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "usuario", type: "object"),
                        new OA\Property(property: "token", type: "string", example: "1|abc123xyz...")
                    ]
                ),
                new OA\Property(property: "statusCode", type: "integer", example: 200)
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 401, description: "Credenciales incorrectas")]
    #[OA\Response(response: 500, description: "Error interno del servidor")]
    public function login(Request $request)
    {
        try {
            // Validación de entrada
            $validator = Validator::make($request->all(), [
                'correo' => 'required|string|email|max:255',
                'password' => 'required|string|min:6'
            ], [
                'correo.required' => 'El correo es requerido',
                'correo.email' => 'El correo debe ser una dirección válida',
                'password.required' => 'La contraseña es requerida',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            // Buscar usuario por correo
            $usuario = UsuariosModel::where('correo', $request->correo)->first();

            // Verificar credenciales
            if (!$usuario || !Hash::check($request->password, $usuario->contraseña)) {
                // Usar mensaje genérico para no revelar si el usuario existe
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales incorrectas',
                    'statusCode' => 401
                ], 401);
            }

            // Revocar tokens anteriores (opcional, para mayor seguridad)
            // $usuario->tokens()->delete();

            // Crear nuevo token
            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'usuario' => [
                        'id' => $usuario->id,
                        'nombre' => $usuario->nombre,
                        'correo' => $usuario->correo,
                        'tipo' => $usuario->tipo
                    ],
                    'token' => $token
                ],
                'statusCode' => 200
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            // Error de base de datos
            \Log::error('Error de base de datos en login: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'sql' => $e->getSql() ?? 'N/A'
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error de conexión con la base de datos. Por favor, intente más tarde.',
                'statusCode' => 500
            ], 500);
        } catch (\Exception $e) {
            // Error genérico
            \Log::error('Error en login: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor. Por favor, contacte al administrador.',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Obtener usuario autenticado
     * 
     * MODIFICADO: 2025-11-18 21:00:00
     * Cambio: Mejorado manejo de errores de autenticación
     * Razón: Evitar exponer detalles técnicos en errores de autenticación
     */
    #[OA\Get(
        path: "/user",
        summary: "Obtener usuario autenticado",
        description: "Retorna la información del usuario actualmente autenticado mediante token Bearer",
        tags: ["Usuarios"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Usuario autenticado obtenido exitosamente")]
    #[OA\Response(response: 401, description: "No autenticado o token inválido")]
    public function user(Request $request)
    {
        try {
            // Verificar si el usuario está autenticado
            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autenticado. Por favor, inicie sesión.',
                    'statusCode' => 401
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'data' => $usuario,
                'statusCode' => 200
            ], 200);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Error específico de autenticación
            return response()->json([
                'status' => 'error',
                'message' => 'Token de autenticación inválido o expirado',
                'statusCode' => 401
            ], 401);
        } catch (\Exception $e) {
            // Log del error para debugging (no exponer detalles al cliente)
            \Log::error('Error en endpoint /user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor. Por favor, contacte al administrador.',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Cerrar sesión
     * 
     * MODIFICADO: 2025-11-18 21:00:00
     * Cambio: Mejorado manejo de errores y seguridad
     * Razón: Asegurar revocación correcta de tokens
     */
    #[OA\Post(
        path: "/logout",
        summary: "Cerrar sesión",
        description: "Revoca todos los tokens de acceso del usuario autenticado",
        tags: ["Usuarios"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Sesión cerrada exitosamente")]
    #[OA\Response(response: 401, description: "No autenticado")]
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

            // Revocar todos los tokens del usuario
            $usuario->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada exitosamente',
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error en logout: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar sesión. Por favor, intente nuevamente.',
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

