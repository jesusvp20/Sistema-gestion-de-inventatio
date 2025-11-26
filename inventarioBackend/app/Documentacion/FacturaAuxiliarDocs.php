<?php

namespace App\Documentacion;

use OpenApi\Attributes as OA;

/**
 * Documentación Swagger para Endpoints Auxiliares de Factura
 * MODIFICADO: 2025-11-19
 * Cambio: Extraído de DetalleFacturaController para modularizar
 */
class FacturaAuxiliarDocs
{
    #[OA\Get(
        path: "/facturas/productos/listar",
        tags: ["Facturas"],
        summary: "Listar productos para facturación",
        parameters: [
            new OA\Parameter(name: "q", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
        ]
    )]
    #[OA\Response(response: 200, description: "Lista de productos")]
    public function listarProductos() {}

    #[OA\Get(
        path: "/facturas/productos/{id}",
        tags: ["Facturas"],
        summary: "Mostrar producto",
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    )]
    #[OA\Response(response: 200, description: "Producto encontrado")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function mostrarProducto() {}

    #[OA\Get(
        path: "/facturas/clientes/listar",
        tags: ["Facturas"],
        summary: "Listar clientes para facturación",
        parameters: [
            new OA\Parameter(name: "q", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
        ]
    )]
    #[OA\Response(response: 200, description: "Lista de clientes")]
    public function listarClientes() {}
}

