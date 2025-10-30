<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Este archivo contiene únicamente anotaciones OpenAPI para rutas
 * que ya existen en el proyecto pero que no estaban apareciendo en Swagger.
 * No se usa en la ejecución de la aplicación.
 */
class DocsExtra
{
    // Ventas
    #[OA\Get(path: "/ventas", tags: ["Ventas"], summary: "Listar ventas", description: "Obtiene una lista de ventas con filtros por fecha y paginación", parameters: [
        new OA\Parameter(name: "fecha_inicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
        new OA\Parameter(name: "fecha_fin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
        new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
    ])]
    #[OA\Response(response: 200, description: "Lista de ventas")]
    public function ventas_index() {}

    #[OA\Post(path: "/ventas", tags: ["Ventas"], summary: "Crear nueva venta", requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["id_cliente", "detalles"], properties: [
        new OA\Property(property: "id_cliente", type: "integer", example: 1),
        new OA\Property(property: "detalles", type: "array", items: new OA\Items(properties: [
            new OA\Property(property: "id_producto", type: "integer", example: 1),
            new OA\Property(property: "cantidad", type: "integer", example: 3)
        ]))
    ])))]
    #[OA\Response(response: 201, description: "Venta creada")]
    public function ventas_store() {}

    #[OA\Put(path: "/ventas/{id}", tags: ["Ventas"], summary: "Actualizar venta", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: "id_cliente", type: "integer", example: 1),
        new OA\Property(property: "detalles", type: "array", items: new OA\Items(properties: [
            new OA\Property(property: "id_producto", type: "integer", example: 1),
            new OA\Property(property: "cantidad", type: "integer", example: 3)
        ]))
    ])))]
    #[OA\Response(response: 200, description: "Venta actualizada")]
    public function ventas_update() {}

    #[OA\Delete(path: "/ventas/{id}", tags: ["Ventas"], summary: "Eliminar venta", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ])]
    #[OA\Response(response: 200, description: "Venta eliminada")]
    public function ventas_destroy() {}

    #[OA\Get(path: "/ventas/historial", tags: ["Ventas"], summary: "Historial de ventas", parameters: [
        new OA\Parameter(name: "fecha_inicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
        new OA\Parameter(name: "fecha_fin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
        new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15))
    ])]
    #[OA\Response(response: 200, description: "Historial obtenido")]
    public function ventas_historial() {}

    // Proveedores
    #[OA\Get(path: "/proveedores", tags: ["Proveedores"], summary: "Listar proveedores")]
    #[OA\Response(response: 200, description: "Lista de proveedores")]
    public function proveedores_index() {}

    #[OA\Post(path: "/proveedores", tags: ["Proveedores"], summary: "Crear proveedor", requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["nombre", "direccion", "telefono", "estado"], properties: [
        new OA\Property(property: "nombre", type: "string"),
        new OA\Property(property: "direccion", type: "string"),
        new OA\Property(property: "telefono", type: "string"),
        new OA\Property(property: "estado", type: "boolean")
    ])))]
    #[OA\Response(response: 201, description: "Proveedor creado")]
    public function proveedores_store() {}

    #[OA\Put(path: "/proveedores/{id}", tags: ["Proveedores"], summary: "Actualizar proveedor", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ])]
    #[OA\Response(response: 200, description: "Proveedor actualizado")]
    public function proveedores_update() {}

    #[OA\Delete(path: "/proveedores/{id}", tags: ["Proveedores"], summary: "Eliminar proveedor", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ])]
    #[OA\Response(response: 200, description: "Proveedor eliminado")]
    public function proveedores_destroy() {}

    #[OA\Patch(path: "/proveedores/{id}/estado", tags: ["Proveedores"], summary: "Cambiar estado del proveedor", parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ])]
    #[OA\Response(response: 200, description: "Estado actualizado")]
    public function proveedores_estado() {}

    #[OA\Get(path: "/proveedores/buscar", tags: ["Proveedores"], summary: "Buscar proveedores por nombre", parameters: [
        new OA\Parameter(name: "nombre", in: "query", required: true, schema: new OA\Schema(type: "string"))
    ])]
    #[OA\Response(response: 200, description: "Resultados de búsqueda")]
    public function proveedores_buscar() {}

    #[OA\Get(path: "/proveedores/activos", tags: ["Proveedores"], summary: "Listar proveedores activos")]
    #[OA\Response(response: 200, description: "Lista de proveedores activos")]
    public function proveedores_activos() {}
}



