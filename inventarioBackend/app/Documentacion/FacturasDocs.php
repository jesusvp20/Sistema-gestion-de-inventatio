<?php

namespace App\Documentacion;

use OpenApi\Attributes as OA;

/**
 * Documentación Swagger para Facturas
 * MODIFICADO: 2025-11-19
 * Cambio: Extraído de DetalleFacturaController para modularizar
 */
class FacturasDocs
{
    #[OA\Get(
        path: "/facturas",
        tags: ["Facturas"],
        summary: "Listar facturas con detalles",
        parameters: [
            new OA\Parameter(name: "fecha_inicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "fecha_fin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "cliente", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "estado", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "order_by", in: "query", required: false, schema: new OA\Schema(type: "string", default: "fecha")),
            new OA\Parameter(name: "order_dir", in: "query", required: false, schema: new OA\Schema(type: "string", default: "desc")),
            new OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
        ]
    )]
    #[OA\Response(response: 200, description: "Lista paginada de facturas")]
    public function listar() {}


    #[OA\Post(
        path: "/facturas",
        tags: ["Facturas"],
        summary: "Crear factura",
        description: "Crea una nueva factura con sus detalles de productos",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["cliente_id", "detalles"],
                properties: [
                    new OA\Property(property: "cliente_id", type: "integer", example: 1),
                    new OA\Property(property: "proveedor_id", type: "integer", example: 1, nullable: true, description: "ID del proveedor (opcional)"),
                    new OA\Property(property: "numero_facturas", type: "string", example: "F-000123", nullable: true),
                    new OA\Property(property: "detalles", type: "array", items: new OA\Items(
                        required: ["producto_id", "cantidad"],
                        properties: [
                            new OA\Property(property: "producto_id", type: "integer", example: 1),
                            new OA\Property(property: "cantidad", type: "integer", example: 2)
                        ]
                    ))
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Factura creada exitosamente")]
    #[OA\Response(response: 400, description: "Datos inválidos o stock insuficiente")]
    public function crear() {}

    #[OA\Put(
        path: "/facturas/{id}",
        tags: ["Facturas"],
        summary: "Actualizar factura",
        description: "Actualiza una factura existente. Puede actualizar los datos de la factura y/o sus detalles. Los detalles se actualizan enviando el array 'detalles' con los detalles a modificar (con 'id' para actualizar) o nuevos (sin 'id' para crear).",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: "cliente_id", type: "integer", example: 1),
            new OA\Property(property: "proveedor_id", type: "integer", example: 1, nullable: true, description: "ID del proveedor (opcional)"),
            new OA\Property(property: "numero_facturas", type: "string", example: "F-000123"),
            new OA\Property(property: "fecha", type: "string", format: "date", example: "2025-11-19"),
            new OA\Property(property: "estado", type: "string", enum: ["pendiente", "disponible", "agotado"], description: "Estado de la factura: pendiente, disponible o agotado (acepta mayúsculas y minúsculas)"),
            new OA\Property(property: "detalles", type: "array", description: "Array opcional de detalles. Incluir 'id' para actualizar detalle existente, omitir 'id' para crear nuevo detalle", items: new OA\Items(properties: [
                new OA\Property(property: "id", type: "integer", example: 1, description: "ID del detalle (opcional, solo para actualizar)"),
                new OA\Property(property: "producto_id", type: "integer", example: 1),
                new OA\Property(property: "cantidad", type: "integer", example: 2)
            ]))
        ]))
    )]
    #[OA\Response(response: 200, description: "Factura actualizada exitosamente")]
    #[OA\Response(response: 400, description: "Datos inválidos")]
    #[OA\Response(response: 404, description: "Factura no encontrada")]
    public function actualizar() {}

    #[OA\Delete(
        path: "/facturas/{id}",
        tags: ["Facturas"],
        summary: "Eliminar factura",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(response: 200, description: "Factura eliminada exitosamente")]
    #[OA\Response(response: 404, description: "Factura no encontrada")]
    public function eliminar() {}
}
