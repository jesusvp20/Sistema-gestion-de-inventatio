<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Documentación de endpoints de Productos
 * 
 * Fecha de creación: 2025-11-18 23:30:00
 * Razón: Documentar API de productos en Swagger
 */
class ProductosDocs
{
    #[OA\Get(
        path: "/productos",
        tags: ["Productos"],
        summary: "Listar productos",
        description: "Obtiene la lista de todos los productos del inventario",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Lista de productos")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "No se encontraron productos")]
    public function productos_index() {}

    #[OA\Post(
        path: "/productos",
        tags: ["Productos"],
        summary: "Crear producto",
        description: "Crea un nuevo producto en el inventario",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "codigoProducto", type: "string", example: "PROD-001"),
                    new OA\Property(property: "nombre", type: "string", example: "Laptop Dell"),
                    new OA\Property(property: "descripcion", type: "string", example: "Laptop 15 pulgadas"),
                    new OA\Property(property: "precio", type: "number", example: 12999.99),
                    new OA\Property(property: "cantidad", type: "integer", example: 25),
                    new OA\Property(property: "categoria", type: "string", example: "Electrónica")
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Producto creado")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function productos_store() {}

    #[OA\Get(
        path: "/productos/{id}",
        tags: ["Productos"],
        summary: "Mostrar producto",
        description: "Obtiene la información de un producto específico",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Producto encontrado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function productos_show() {}

    #[OA\Put(
        path: "/productos/{id}",
        tags: ["Productos"],
        summary: "Actualizar producto",
        description: "Actualiza la información de un producto existente",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Laptop Dell Actualizada"),
                    new OA\Property(property: "precio", type: "number", example: 13999.99),
                    new OA\Property(property: "cantidad", type: "integer", example: 30)
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: "Producto actualizado")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function productos_update() {}

    #[OA\Delete(
        path: "/productos/{id}",
        tags: ["Productos"],
        summary: "Eliminar producto",
        description: "Elimina un producto del inventario",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Producto eliminado")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function productos_destroy() {}
}

