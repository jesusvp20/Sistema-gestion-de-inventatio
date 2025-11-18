<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Solo documenta los endpoints de Usuarios y Autenticación.
 * 
 * MODIFICADO: 2025-11-18 21:00:00
 * Cambio: Agregada seguridad Bearer a endpoints protegidos
 * Razón: Documentar correctamente la autenticación requerida
 */
class UsuariosDocs
{
    // Autenticación
    #[OA\Post(
        path: "/register",
        tags: ["Autenticación", "Usuarios"],
        summary: "Registrar usuario",
        description: "Crea un nuevo usuario en el sistema. No requiere autenticación.",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/RegisterRequest"))
    )]
    #[OA\Response(response: 201, description: "Usuario registrado exitosamente")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 500, description: "Error interno del servidor")]
    public function auth_register() {}

    #[OA\Post(
        path: "/login",
        tags: ["Autenticación"],
        summary: "Iniciar sesión",
        description: "Autentica un usuario y devuelve un token Bearer para usar en endpoints protegidos. No requiere autenticación previa.",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/LoginRequest"))
    )]
    #[OA\Response(response: 200, description: "Login exitoso, token generado")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 401, description: "Credenciales incorrectas")]
    #[OA\Response(response: 500, description: "Error interno del servidor")]
    public function auth_login() {}

    #[OA\Get(
        path: "/user",
        tags: ["Autenticación", "Usuarios"],
        summary: "Usuario autenticado actual",
        description: "Obtiene la información del usuario actualmente autenticado. Requiere token Bearer.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Usuario autenticado obtenido exitosamente")]
    #[OA\Response(response: 401, description: "No autenticado o token inválido")]
    #[OA\Response(response: 500, description: "Error interno del servidor")]
    public function auth_user() {}

    #[OA\Post(
        path: "/logout",
        tags: ["Autenticación"],
        summary: "Cerrar sesión",
        description: "Revoca todos los tokens de acceso del usuario autenticado. Requiere token Bearer.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Sesión cerrada exitosamente")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 500, description: "Error interno del servidor")]
    public function auth_logout() {}

    // CRUD Usuarios
    #[OA\Get(
        path: "/usuarios",
        tags: ["Usuarios"],
        summary: "Listar usuarios",
        description: "Obtiene la lista de todos los usuarios del sistema. Requiere autenticación.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Lista de usuarios obtenida exitosamente")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "No se encontraron usuarios")]
    public function usuarios_index() {}

    #[OA\Post(
        path: "/usuarios",
        tags: ["Usuarios"],
        summary: "Crear usuario",
        description: "Crea un nuevo usuario en el sistema. Requiere autenticación.",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
            new OA\Property(property: "correo", type: "string", format: "email", example: "juan@ejemplo.com"),
            new OA\Property(property: "contraseña", type: "string", format: "password", example: "password123"),
            new OA\Property(property: "tipo", type: "string", example: "usuario")
        ]))
    )]
    #[OA\Response(response: 201, description: "Usuario creado exitosamente")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function usuarios_store() {}

    #[OA\Get(
        path: "/usuarios/{id}",
        tags: ["Usuarios"],
        summary: "Mostrar usuario",
        description: "Obtiene la información de un usuario específico. Requiere autenticación.",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Usuario encontrado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "Usuario no encontrado")]
    public function usuarios_show() {}

    #[OA\Put(
        path: "/usuarios/{id}",
        tags: ["Usuarios"],
        summary: "Actualizar usuario",
        description: "Actualiza la información de un usuario existente. Requiere autenticación.",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
            new OA\Property(property: "correo", type: "string", format: "email", example: "juan@ejemplo.com"),
            new OA\Property(property: "tipo", type: "string", example: "usuario")
        ]))
    )]
    #[OA\Response(response: 200, description: "Usuario actualizado exitosamente")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "Usuario no encontrado")]
    public function usuarios_update() {}

    #[OA\Delete(
        path: "/usuarios/{id}",
        tags: ["Usuarios"],
        summary: "Eliminar usuario",
        description: "Elimina un usuario del sistema. Requiere autenticación.",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Usuario eliminado exitosamente")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "Usuario no encontrado")]
    public function usuarios_destroy() {}
}



