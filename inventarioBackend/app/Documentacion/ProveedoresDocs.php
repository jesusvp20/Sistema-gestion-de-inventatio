<?php

namespace App\documentacion;

use OpenApi\Attributes as OA;

/**
 * Documentación de endpoints de Proveedores
 * 
 * Actualizado: 2025-01-27
 * Cambios: Se agregaron todos los endpoints faltantes y se corrigieron los campos del requestBody
 */
class ProveedoresDocs
{
    #[OA\Get(
        path: "/proveedores",
        tags: ["Proveedores"],
        summary: "Listar proveedores",
        description: "Obtiene la lista de todos los proveedores registrados"
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de proveedores obtenida exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
                            new OA\Property(property: "direccion", type: "string", example: "Calle 123 #45-67"),
                            new OA\Property(property: "telefono", type: "string", example: "3001234567"),
                            new OA\Property(property: "estado", type: "boolean", example: true)
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(response: 404, description: "No hay proveedores registrados")]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function proveedores_index() {}

    #[OA\Post(
        path: "/proveedores",
        tags: ["Proveedores"],
        summary: "Crear proveedor",
        description: "Crea un nuevo proveedor en el sistema",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "direccion", "telefono", "estado"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", maxLength: 255, example: "Proveedor ABC"),
                    new OA\Property(property: "direccion", type: "string", maxLength: 200, example: "Calle 123 #45-67"),
                    new OA\Property(property: "telefono", type: "string", maxLength: 50, example: "3001234567"),
                    new OA\Property(property: "estado", type: "boolean", example: true)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Proveedor creado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Proveedor creado exitosamente"),
                new OA\Property(
                    property: "data",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
                        new OA\Property(property: "direccion", type: "string", example: "Calle 123 #45-67"),
                        new OA\Property(property: "telefono", type: "string", example: "3001234567"),
                        new OA\Property(property: "estado", type: "boolean", example: true)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Datos inválidos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Datos invalidos"),
                new OA\Property(property: "errors", type: "object")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function proveedores_store() {}

    #[OA\Get(
        path: "/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Mostrar proveedor",
        description: "Obtiene la información de un proveedor específico por su ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del proveedor",
                schema: new OA\Schema(type: "integer", example: 27)
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Proveedor encontrado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 27),
                        new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
                        new OA\Property(property: "direccion", type: "string", example: "Calle 123 #45-67"),
                        new OA\Property(property: "telefono", type: "string", example: "3001234567"),
                        new OA\Property(property: "estado", type: "boolean", example: true),
                        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2025-01-27T10:00:00.000000Z"),
                        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2025-01-27T10:00:00.000000Z")
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Proveedor no encontrado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Proveedor no encontrado")
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Error del servidor",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Error al obtener el proveedor"),
                new OA\Property(property: "error", type: "string", example: "Error interno del servidor")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_show() {}

    #[OA\Put(
        path: "/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Actualizar proveedor",
        description: "Actualiza la información de un proveedor existente",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del proveedor a actualizar",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", maxLength: 255, example: "Proveedor XYZ"),
                    new OA\Property(property: "direccion", type: "string", maxLength: 200, example: "Carrera 10 #20-30"),
                    new OA\Property(property: "telefono", type: "string", maxLength: 50, example: "3101112233"),
                    new OA\Property(property: "estado", type: "boolean", example: true)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Proveedor actualizado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Proveedor actualizado exitosamente"),
                new OA\Property(
                    property: "data",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nombre", type: "string", example: "Proveedor XYZ"),
                        new OA\Property(property: "direccion", type: "string", example: "Carrera 10 #20-30"),
                        new OA\Property(property: "telefono", type: "string", example: "3101112233"),
                        new OA\Property(property: "estado", type: "boolean", example: true)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Proveedor no encontrado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Proveedor no ha sido encontrado")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Datos inválidos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Datos invalidos"),
                new OA\Property(property: "errors", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Error del servidor",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Error al actualizar el proveedor"),
                new OA\Property(property: "error", type: "string", example: "Error interno del servidor")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_update() {}

    #[OA\Delete(
        path: "/proveedores/{id}",
        tags: ["Proveedores"],
        summary: "Eliminar proveedor",
        description: "Elimina un proveedor del sistema. No se puede eliminar si tiene facturas asociadas.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del proveedor a eliminar",
                schema: new OA\Schema(type: "integer", example: 27)
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Proveedor eliminado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "El proveedor se ha eliminado correctamente"),
                new OA\Property(property: "warning", type: "string", nullable: true, example: "Se encontraron 5 producto(s) asociado(s) que ahora no tienen proveedor asignado")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Proveedor no encontrado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Proveedor no encontrado")
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: "No se puede eliminar: tiene facturas asociadas",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "No se puede eliminar el proveedor porque tiene facturas asociadas"),
                new OA\Property(
                    property: "details",
                    type: "object",
                    properties: [
                        new OA\Property(property: "facturas_asociadas", type: "integer", example: 3),
                        new OA\Property(property: "sugerencia", type: "string", example: "Elimine o actualice las facturas asociadas antes de eliminar el proveedor")
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Error del servidor",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Error al eliminar el proveedor"),
                new OA\Property(property: "error", type: "string", example: "Error interno del servidor")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_destroy() {}

    #[OA\Patch(
        path: "/proveedores/{id}/estado",
        tags: ["Proveedores"],
        summary: "Cambiar estado del proveedor",
        description: "Cambia el estado activo/inactivo de un proveedor (alterna el valor booleano)",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del proveedor",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Estado actualizado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Estado del proveedor ha sido actualizado"),
                new OA\Property(
                    property: "data",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
                        new OA\Property(property: "estado", type: "boolean", example: false)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Proveedor no encontrado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Proveedor no encontrado")
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Error del servidor",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Error al cambiar el estado del proveedor"),
                new OA\Property(property: "error", type: "string", example: "Error interno del servidor")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function proveedores_cambiar_estado() {}

    #[OA\Get(
        path: "/proveedores/buscar",
        tags: ["Proveedores"],
        summary: "Buscar proveedores por nombre",
        description: "Busca proveedores cuyo nombre coincida parcialmente con el término de búsqueda",
        parameters: [
            new OA\Parameter(
                name: "nombre",
                in: "query",
                required: true,
                description: "Término de búsqueda para el nombre del proveedor",
                schema: new OA\Schema(type: "string", example: "ABC")
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Resultados de búsqueda encontrados",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
                            new OA\Property(property: "direccion", type: "string", example: "Calle 123 #45-67"),
                            new OA\Property(property: "telefono", type: "string", example: "3001234567"),
                            new OA\Property(property: "estado", type: "boolean", example: true)
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Parámetros inválidos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "El parámetro nombre es requerido"),
                new OA\Property(property: "errors", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Sin resultados",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "No se encontraron proveedores con ese nombre")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function proveedores_buscar() {}

    #[OA\Get(
        path: "/proveedores/activos",
        tags: ["Proveedores"],
        summary: "Listar proveedores activos",
        description: "Obtiene la lista de todos los proveedores con estado activo (estado = true)"
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de proveedores activos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
                            new OA\Property(property: "direccion", type: "string", example: "Calle 123 #45-67"),
                            new OA\Property(property: "telefono", type: "string", example: "3001234567"),
                            new OA\Property(property: "estado", type: "boolean", example: true)
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "No hay proveedores activos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "No hay proveedores activos")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autenticado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function proveedores_activos() {}
}

