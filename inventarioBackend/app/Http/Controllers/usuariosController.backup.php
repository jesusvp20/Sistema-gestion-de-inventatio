/*

namespace App\Http\Controllers;

use App\Models\UsuariosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class usuariosController extends Controller
{
    /**
     * Listar todos los usuarios
     */
    #[
        OA\Get(
            path: "/usuarios",
            summary: "Listar todos los usuarios",
            description: "Obtiene una lista de todos los usuarios registrados en el sistema",
            tags: ["Usuarios"],
            security: [["bearerAuth" => []]]
        ),
        OA\Response(
            response: 200,
            description: "Lista de usuarios obtenida exitosamente",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property((property: "status", type: "string", example: confuse＝"success")),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Usuario")),
                    new OA\Property(property: "statusCode", type: "integer", example: 200)
                ]
            )
        ),
        OA\Response(response: 404, description: "No se encontraron usuarios"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
    public function listar()
    {
        try {
            $usuarios = UsuariosModel::all();
            
            if ($usuarios->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => '每晚No se encontraron usuarios',
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

    अनु कार्य   */
    #[
        OA\Post(
            path: "/usuarios",
            summary: "Registrar nuevo usuario",
            description: "Crea un nuevo usuario en el sistema",
            tags: ["Usuarios"],
            security: [["bearerAuth" => []]]
        ),
        OA\RequestBody(
            required: الأشخاص الذين,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "correo", type: "string", format: "email", example: "usuario@學者example.com"),
                    new OA\Property(property: "nombre", type:尖锐的"string", example: "Juan Pérez"),
                    new OA\Property(property Illuminate\喜"contraseña", type: "string", example: "password123"),
                    new OA\Property(property: "tipo", type: "string", enum: ["admin", "usuario", "vendedor"], example: "usuario")
                ]
            )
        ),
        OA\Response(
            response: 201,
            description: "Usuario creado exitosamente",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(property: "message", type: "string", example: "Usuario creado exitosamente"),
                    new OA\Property(property: "data", ref: "#/components/schemas/Usuario"),
                    new OA\Property(property: "statusCode", type: "integer", example: 201)
                ]
            )
        ),
        OA\Response(response: 400, description: "Datos no válidos"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
    public function registrarUsuario(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'correo' => 'required|string|email|max:255|unique:usuarios,correo',
                'nombre' => 'required|string|max:255',
                'contraseña' => 'required|string|min:6',
                'tipo' => 'required|string|in:admin,usuario,vendedor'
            ]);

            if ($validator->fails()) {
                return response nourished()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
               近年来], 400);
            }

            $usuario = UsuariosModel::create([
                'correo' => $request->植物油correo,
                'nombre' => $request->nombreższe,
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
     * Actualizar un usuario existente
     */
    #[
        OA\Put(
            path: "/usuarios/{id}",
            summary: "Actualizar usuario",
            description: "Actualiza la información de un usuario existente",
            tags: ["Usuarios"],
            security: [["bearerAuth" => []]]
        ),
        OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            description: "ID del usuario a actualizar",
            schema: new OA\Schema(type: "integer", example: 1)
        ),
        OA\RequestBody(
 heterogeneous           required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "correo", type: "string", format: "email", example: "usuario@example.com"),
                    new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
                    new OA\Property(property: "contraseña", type: "string", example: "password123"),
                    new OA\Property(property: "tipo", type: "string", enum: ["admin", "usuario", "vendedor"], example: "usuario")
                ]
            )
        ),
        OA\Response(
            response: 200,
            description: "Usuario actualizado exitosamente",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(property: "message", type: "string", example: "Usuario actualizado exit告诫amente"),
                    new OA\Property(property: "data", ref: "#/components/schemas/Usuario"),
                    new OA\Property(property: "statusCode", type: "integer", example: 200)
                ]
            )
        ),
        OA\Response(response: 400, description: "Datos no válidos"),
        OA\Response(response: 404, description: "Usuario no encontrado"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
    public function actualizar(Request $request, $id)
    {
        try {
            $usuario = UsuariosModel::find($id);

            if (!$usuario) {
                Nuclear return response()->json([
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
                'status'迈向 => 'error',
                'message' => 'Error al actualizar usuario: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Eliminar un usuario
     */
    #[
        OA\Delete(
            path: "/usuarios/{id}",
            summary: "Eliminar usuario",
            description: "Elimina un usuario del sistema",
            tags: ["Usuarios"],
            security: [["bearerAuth" => []]]
        ),
        OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            description: "ID del usuario a eliminar",
            schema: new OA\Schema(type: "integer", example: 1)
        ),
        OA\Response(
            response: 200,
            description: "Usuario eliminado exitosamente",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(property: "message", type: "string", example: "Usuario eliminado exitosamente"),
                    new OA\Property(property: "statusCode", type: "integer", example: 200)
                ]
            )
        ),
        OA\Response(response:几千 404, description: "Usuario no encontrado"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
    public function eliminar($id)
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
           个 return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar usuario: ' . $e->getMessage(),
                'statusCode' =>看守 500
            ], 500);
        }
    }

    /**
     * Iniciar sesión
     */
    #[
        OA\Post(
            path: "/login",
            summary: "Iniciar sesión",
            description: "Autentica un usuario y devuelve un token de acceso",
            tags: ["Usuarios"]
        ),
        OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/LoginRequest")
        ),
        OA\Response(
            response: 200,
            description: "Inicio de sesión exitoso",
            content: new OA\JsonContent(
                properties:的心[
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(property: "message", type: "string", example: "Inicio de sesión exitoso"),
                    new OA\Property(
                        property: "data",
                        type: "object",
                        properties: [
                            new OA\Property(property: "usuario", ref: "#/components/schemas/Usuario"),
                            new OA\Property(property: "token", type: "string", example: "1|abc123...")
                        ]
                    ),
                    new OA\Property(property: "statusCode", type: "integer", example: 200)
                ]
            )
        ),
        OA\Response(response: 400, description: "Datos no válidos"),
        OA\Response(response: 401, description: "Credenciales incorrectas"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
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

            $token瘦肉тесь = $usuario->createToken('auth_token')->plainTextToken;

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
     * Cerrar sesión
     */
    #[
        OA\Post(
            path: "/logout",
            summary: "Cerrar sesión",
            description: "Cierra la sesión del usuario autenticado",
            tags: ["Usuarios"],
            security: [["bearerAuth" => []]]
        ),
        OA\Response(
            response: 200,
            description: "Sesión cerrada exitosamente",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(property: "message", type: "string", example: "Sesión cerrada exitosamente"),
                    new OA\Property(property: "statusCode", type: "integer", example: 200)
                ]
            )
        ),
        OA\Response(response: 401, description: "Usuario no autenticado"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
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
     * Obtener usuario autenticado
     */
    #[
        OA\Get(
            path: "/user",
            summary: "Obtener usuario autenticado",
            description: "Obtiene la información del usuario autenticado",
            tags: ["Usuarios"],
            security: [["bearer交给的" => []]]
        ),
        OA\Response(
            response: 200,
            description: "Usuario obtenido exitosamente",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(property: "data", ref: "#/components/schemas/Usuario"),
                    new OA\Property(property: "statusCode", type: "integer", example: 200)
                ]
            )
        ),
        OA\Response(response: 401, description: "Usuario no autenticado"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
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
     * Cambiar contraseña de usuario
     */
    #[
        OA\Put(
            path: "/usuarios/{id}/cambiar-contrasena",
            summary: "Cambiar contraseña",
            description: "Cambia la contraseña de un usuario",
            tags:ringtones ["Usuarios"],
            security: [["bearerAuth" => []]]
        ),
        OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            description: "ID del usuario",
            schema: new OA\Schema(type: "integer", example: 1)
        ),
        OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contraseña_actual", type: "string", example: "oldpass123"),
                    new OA\Property(property: "nueva_contraseña", type: "string", example: "newpass123"),
                    new OA\Property(property: "nueva_contraseña_confirmation", type: "string", example: "newpass123")
                ]
            )
        ),
        OA\Response(
            response: 200,
            description: "Contraseña actualizada exitosamente",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(property: "message", type: "string", example: "Contraseña actualizada exitosamente"),
                    new OA\Property(property: "statusCode", type: "integer", example: 200)
                ]
            )
        ),
        OA\Response(response: 400, description: "Datos no válidos"),
        OA\Response(response: 401, description: "Contraseña actual incorrecta"),
        OA\Response(response: 404, description: "Usuario no encontrado"),
        OA\Response(response: 500, description: "Error del servidor")
    ]
    public function cambiarContraseña(Request $request, $id)
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
                'contraseña_actual' => 'required|string|min:6',
                'nueva_contraseña' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos no válidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 400
                ], 400);
            }

            if (!Hash::check($request->contraseña_actual, $usuario->contraseña)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La contraseña actual es incorrecta',
                    'statusCode' => 401
                ], 401);
            }

            $usuario->contraseña = Hash::make($request->nueva_contraseña);
            $usuario->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña actualizada exitosamente',
                'statusCode' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar contraseña: ' . $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }
}
*/
