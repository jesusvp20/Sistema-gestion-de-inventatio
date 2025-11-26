<?php

namespace App\documentacion;

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
        description: "Obtiene la lista de todas las ventas con sus detalles completos (cliente y productos). Incluye filtros opcionales por fecha.",
        parameters: [
            new OA\Parameter(name: "fecha_inicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date"), description: "Fecha de inicio para filtrar ventas"),
            new OA\Parameter(name: "fecha_fin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date"), description: "Fecha de fin para filtrar ventas"),
            new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15), description: "Número de resultados por página")
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de ventas con detalles",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "object", description: "Datos paginados con ventas que incluyen cliente y detalles de productos")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 404, description: "No se encontraron ventas")]
    public function ventas_index() {}

    #[OA\Post(
        path: "/ventas",
        tags: ["Ventas"],
        summary: "Crear venta",
        description: "Registra una nueva venta con sus detalles de productos. Actualiza automáticamente el stock de los productos vendidos.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["id_cliente", "detalles"],
                properties: [
                    new OA\Property(property: "id_cliente", type: "integer", example: 1, description: "ID del cliente que realiza la compra"),
                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        description: "Array de detalles de venta. Cada detalle incluye el producto y la cantidad vendida.",
                        items: new OA\Items(
                            required: ["id_producto", "cantidad"],
                            properties: [
                                new OA\Property(property: "id_producto", type: "integer", example: 1, description: "ID del producto a vender"),
                                new OA\Property(property: "cantidad", type: "integer", example: 2, description: "Cantidad del producto a vender (mínimo 1)")
                            ]
                        )
                    )
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Venta creada exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Venta creada exitosamente"),
                new OA\Property(property: "data", ref: "#/components/schemas/Venta", description: "Venta creada con cliente y detalles de productos")
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Datos inválidos o stock insuficiente")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_store() {}

    #[OA\Get(
        path: "/ventas/{id}",
        tags: ["Ventas"],
        summary: "Mostrar venta",
        description: "Obtiene los detalles completos de una venta específica, incluyendo información del cliente y todos los productos vendidos con sus cantidades y precios.",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID de la venta a consultar")
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Venta encontrada con detalles completos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Venta obtenida exitosamente"),
                new OA\Property(property: "data", ref: "#/components/schemas/Venta", description: "Venta con cliente y detalles de productos"),
                new OA\Property(property: "statusCode", type: "integer", example: 200)
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Venta no encontrada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_show() {}

    #[OA\Get(
        path: "/ventas/historial",
        tags: ["Ventas"],
        summary: "Historial de ventas",
        description: "Obtiene el historial completo de ventas con filtros opcionales",
        parameters: [
            new OA\Parameter(name: "fecha_inicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "fecha_fin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
        ]
    )]
    #[OA\Response(response: 200, description: "Historial de ventas")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_historial() {}

    #[OA\Put(
        path: "/ventas/{id}",
        tags: ["Ventas"],
        summary: "Actualizar venta",
        description: "Actualiza una venta existente. Puedes actualizar solo el cliente, solo los productos, o ambos. IMPORTANTE: Si envías 'detalles', se REEMPLAZAN TODOS los productos anteriores. Los campos son opcionales, pero debes enviar al menos uno.",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID de la venta a actualizar")
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "id_cliente",
                        type: "integer",
                        example: 1,
                        description: "ID del cliente (OPCIONAL - solo si quieres cambiar el cliente)"
                    ),
                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        description: "Array de productos. Si envías esto, REEMPLAZA todos los productos anteriores. Cada producto debe tener id_producto y cantidad.",
                        items: new OA\Items(
                            required: ["id_producto", "cantidad"],
                            properties: [
                                new OA\Property(property: "id_producto", type: "integer", example: 1, description: "ID del producto a vender"),
                                new OA\Property(property: "cantidad", type: "integer", example: 4, description: "Cantidad del producto (mínimo 1)")
                            ]
                        ),
                        example: [
                            ["id_producto" => 1, "cantidad" => 4],
                            ["id_producto" => 2, "cantidad" => 2]
                        ]
                    )
                ],
                example: [
                    "id_cliente" => 1,
                    "detalles" => [
                        ["id_producto" => 1, "cantidad" => 4]
                    ]
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Venta actualizada exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Venta actualizada exitosamente"),
                new OA\Property(property: "data", ref: "#/components/schemas/Venta")
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Datos inválidos o stock insuficiente")]
    #[OA\Response(response: 404, description: "Venta no encontrada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_update() {}

    #[OA\Delete(
        path: "/ventas/{id}",
        tags: ["Ventas"],
        summary: "Eliminar venta",
        description: "Elimina una venta y restaura el stock de todos los productos asociados en los detalles de la venta",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID de la venta a eliminar")
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Venta eliminada exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Venta eliminada exitosamente")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Venta no encontrada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function ventas_destroy() {}
}

