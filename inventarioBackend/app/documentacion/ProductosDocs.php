<?php

namespace App\documentacion;

use OpenApi\Attributes as OA;

/**
 * Documentación Swagger para Productos
 * MODIFICADO: 2025-11-19
 * Cambio: Extraído de productosController para modularizar
 */
class ProductosDocs
{
    #[OA\Get(path: "/productos", tags: ["Productos"], summary: "Listar productos")]
    #[OA\Response(response: 200, description: "Lista de productos obtenida exitosamente")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function index() {}

    #[OA\Post(path: "/productos", tags: ["Productos"], summary: "Crear producto")]
    #[OA\Response(response: 201, description: "Producto creado exitosamente")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function store() {}

    #[OA\Get(path: "/productos/{id}", tags: ["Productos"], summary: "Mostrar producto")]
    #[OA\Response(response: 200, description: "Producto obtenido exitosamente")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function show() {}

    #[OA\Put(path: "/productos/{id}", tags: ["Productos"], summary: "Actualizar producto")]
    #[OA\Response(response: 200, description: "Producto actualizado exitosamente")]
    #[OA\Response(response: 400, description: "Datos no válidos")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function update() {}

    #[OA\Delete(path: "/productos/{id}", tags: ["Productos"], summary: "Eliminar producto")]
    #[OA\Response(response: 200, description: "Producto eliminado exitosamente")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function destroy() {}

    #[OA\Patch(path: "/productos/{id}/cambiar-estado", tags: ["Productos"], summary: "Cambiar estado del producto")]
    #[OA\Response(response: 200, description: "Estado del producto actualizado")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    public function cambiarEstado() {}

    #[OA\Get(path: "/productos/buscar", tags: ["Productos"], summary: "Buscar productos por nombre")]
    #[OA\Response(response: 200, description: "Productos encontrados")]
    public function buscar() {}

    #[OA\Get(path: "/productos/disponibles", tags: ["Productos"], summary: "Listar productos disponibles")]
    #[OA\Response(response: 200, description: "Lista de productos activos")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function disponibles() {}

    #[OA\Get(path: "/productos/ordenar", tags: ["Productos"], summary: "Ordenar productos por precio")]
    #[OA\Response(response: 200, description: "Productos ordenados")]
    public function ordenar() {}
}
