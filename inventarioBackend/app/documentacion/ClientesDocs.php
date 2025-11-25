<?php

namespace App\documentacion;

use OpenApi\Attributes as OA;

/**
 * Documentación de endpoints de Clientes
 */
class ClientesDocs
{
    #[OA\Get(
        path: "/clientes",
        tags: ["Clientes"],
        summary: "Listar clientes",
        description: "Obtiene la lista de todos los clientes",
    )]
    #[OA\Response(response: 200, description: "Lista de clientes")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function clientes_index() {}

    #[OA\Post(
        path: "/clientes",
        tags: ["Clientes"],
        summary: "Crear cliente",
        description: "Crea un nuevo cliente",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "email", "identificacion", "telefono"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "juan@email.com", description: "Correo electrónico (también acepta 'correo')"),
                    new OA\Property(property: "correo", type: "string", format: "email", example: "juan@email.com", nullable: true, description: "Alias de 'email'"),
                    new OA\Property(property: "identificacion", type: "string", example: "123456789", description: "Número de identificación del cliente (requerido y único)"),
                    new OA\Property(property: "telefono", type: "string", example: "555-0123"),
                    new OA\Property(property: "estado", type: "boolean", example: true, description: "Estado: true/false o 'activo'/'inactivo'")
                ],
                example: [
                    "nombre" => "Juan Pérez",
                    "correo" => "juan@email.com",
                    "identificacion" => "123456789",
                    "telefono" => "555-0123",
                    "estado" => "activo"
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Cliente creado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function clientes_store() {}

    #[OA\Get(
        path: "/clientes/{id}",
        tags: ["Clientes"],
        summary: "Mostrar cliente",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Cliente encontrado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    public function clientes_show() {}

    #[OA\Put(
        path: "/clientes/{id}",
        tags: ["Clientes"],
        summary: "Actualizar cliente",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
                    new OA\Property(property: "telefono", type: "string", example: "555-0123")
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: "Cliente actualizado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function clientes_update() {}

    #[OA\Delete(
        path: "/clientes/{id}",
        tags: ["Clientes"],
        summary: "Eliminar cliente",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Cliente eliminado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function clientes_destroy() {}

    #[OA\Get(
        path: "/clientes/activos",
        tags: ["Clientes"],
        summary: "Listar clientes activos",
    )]
    #[OA\Response(response: 200, description: "Lista de clientes activos")]
    public function clientes_activos() {}
}

