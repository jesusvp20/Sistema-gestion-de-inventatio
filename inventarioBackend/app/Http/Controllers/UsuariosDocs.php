<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Solo documenta los endpoints de Usuarios y Autenticación.
 */
class UsuariosDocs
{
    // Autenticación
    #[OA\Post(
        path: "/register",
        tags: ["Autenticación", "Usuarios"],
        summary: "Registrar usuario",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/RegisterRequest"))
    )]
    #[OA\Response(response: 201, description: "Usuario registrado")]
    public function auth_register() {}

    #[OA\Post(
        path: "/login",
        tags: ["Autenticación"],
        summary: "Iniciar sesión",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/LoginRequest"))
    )]
    #[OA\Response(response: 200, description: "Login exitoso")]
    public function auth_login() {}

    #[OA\Get(
        path: "/user",
        tags: ["Autenticación", "Usuarios"],
        summary: "Usuario autenticado actual"
    )]
    #[OA\Response(response: 200, description: "Usuario actual")]
    public function auth_user() {}

    #[OA\Post(
        path: "/logout",
        tags: ["Autenticación"],
        summary: "Cerrar sesión"
    )]
    #[OA\Response(response: 200, description: "Sesión cerrada")]
    public function auth_logout() {}

    // CRUD Usuarios
    #[OA\Get(path: "/usuarios", tags: ["Usuarios"], summary: "Listar usuarios")]
    #[OA\Response(response: 200, description: "Lista de usuarios")]
    public function usuarios_index() {}

    #[OA\Post(path: "/usuarios", tags: ["Usuarios"], summary: "Crear usuario", requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "correo", type: "string", format: "email", example: "juan@ejemplo.com"),
        new OA\Property(property: "contraseña", type: "string", format: "password", example: "password123"),
        new OA\Property(property: "tipo", type: "string", example: "usuario")
    ])))]
    #[OA\Response(response: 201, description: "Usuario creado")]
    public function usuarios_store() {}

    #[OA\Get(path: "/usuarios/{id}", tags: ["Usuarios"], summary: "Mostrar usuario", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ])]
    #[OA\Response(response: 200, description: "Usuario encontrado")]
    public function usuarios_show() {}

    #[OA\Put(path: "/usuarios/{id}", tags: ["Usuarios"], summary: "Actualizar usuario", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "correo", type: "string", format: "email", example: "juan@ejemplo.com"),
        new OA\Property(property: "tipo", type: "string", example: "usuario")
    ])))]
    #[OA\Response(response: 200, description: "Usuario actualizado")]
    public function usuarios_update() {}

    #[OA\Delete(path: "/usuarios/{id}", tags: ["Usuarios"], summary: "Eliminar usuario", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ])]
    #[OA\Response(response: 200, description: "Usuario eliminado")]
    public function usuarios_destroy() {}
}



