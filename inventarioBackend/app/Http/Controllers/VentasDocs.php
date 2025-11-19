<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Documentación de endpoints de Ventas
 */
class VentasDocs
{
    #[OA\Get(
        path: "/ventas",
        tags: ["Ventas"],
        summary: "Listar ventas",
        description: "Obtiene la lista de todas las ventas",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Lista de ventas")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_index() {}

    #[OA\Post(
        path: "/ventas",
        tags: ["Ventas"],
        summary: "Crear venta",
        description: "Registra una nueva venta",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id_cliente", type: "integer", example: 1),
                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id_producto", type: "integer", example: 1),
                                new OA\Property(property: "cantidad", type: "integer", example: 2)
                            ]
                        )
                    )
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Venta creada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_store() {}

    #[OA\Get(
        path: "/ventas/{id}",
        tags: ["Ventas"],
        summary: "Mostrar venta",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Venta encontrada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_show() {}

    #[OA\Get(
        path: "/ventas/historial",
        tags: ["Ventas"],
        summary: "Historial de ventas",
        description: "Obtiene el historial completo de ventas",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Historial de ventas")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_historial() {}
}

