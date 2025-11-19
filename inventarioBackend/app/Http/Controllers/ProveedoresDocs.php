<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Documentación de endpoints de Proveedores
 */
class ProveedoresDocs
{
    #[OA\Get(
        path: "/proveedores",
        tags: ["Proveedores"],
        summary: "Listar proveedores",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Lista de proveedores")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_index() {}

    #[OA\Post(
        path: "/proveedores",
        tags: ["Proveedores"],
        summary: "Crear proveedor",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Distribuidora Tech"),
                    new OA\Property(property: "correo", type: "string", example: "ventas@tech.com"),
                    new OA\Property(property: "telefono", type: "string", example: "555-9876"),
                    new OA\Property(property: "direccion", type: "string", example: "Calle Industria 456"),
                    new OA\Property(property: "estado", type: "string", example: "activo")
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Proveedor creado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_store() {}

    #[OA\Get(
        path: "/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Mostrar proveedor",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Proveedor encontrado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_show() {}

    #[OA\Put(
        path: "/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Actualizar proveedor",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Proveedor actualizado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_update() {}

    #[OA\Delete(
        path: "/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Eliminar proveedor",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Proveedor eliminado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_destroy() {}

    #[OA\Get(
        path: "/proveedores/activos",
        tags: ["Proveedores"],
        summary: "Listar proveedores activos",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Lista de proveedores activos")]
    public function proveedores_activos() {}
}

