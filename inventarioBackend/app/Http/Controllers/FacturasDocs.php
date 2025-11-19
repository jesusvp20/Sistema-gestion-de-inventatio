<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Documentación de endpoints de Facturas
 */
class FacturasDocs
{
    #[OA\Get(
        path: "/gestion-facturas",
        tags: ["Facturas"],
        summary: "Listar facturas",
        description: "Obtiene la lista de todas las facturas",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Lista de facturas")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function facturas_index() {}

    #[OA\Get(
        path: "/gestion-facturas/{id}",
        tags: ["Facturas"],
        summary: "Mostrar factura",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Factura encontrada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function facturas_show() {}

    #[OA\Post(
        path: "/gestion-facturas/{id}/archivar",
        tags: ["Facturas"],
        summary: "Archivar factura",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Factura archivada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function facturas_archivar() {}

    #[OA\Delete(
        path: "/gestion-facturas/{id}",
        tags: ["Facturas"],
        summary: "Eliminar factura",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ]
    )]
    #[OA\Response(response: 200, description: "Factura eliminada")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function facturas_destroy() {}
}

