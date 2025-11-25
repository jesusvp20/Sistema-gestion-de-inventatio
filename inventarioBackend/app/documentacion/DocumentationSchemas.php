<?php

/**
 * CAMBIO REALIZADO: 2025-11-24
 * 
 * QUÉ SE CAMBIÓ:
 * - Archivo movido desde app/Http/Controllers/DocumentationSchemas.php a app/documentacion/DocumentationSchemas.php
 * - Namespace actualizado de App\Http\Controllers a App\Documentacion
 * 
 * POR QUÉ SE CAMBIÓ:
 * - Para mantener la organización del código, agrupando todos los archivos de documentación
 *   de Swagger/OpenAPI en la carpeta documentacion junto con los demás archivos de documentación
 *   (UsuariosDocs.php, ProductosDocs.php, ClientesDocs.php, etc.)
 * 
 * FECHA Y HORA: 2025-11-24 11:18:13
 */

namespace App\Documentacion;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Usuario",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "correo", type: "string", example: "juan@ejemplo.com"),
        new OA\Property(property: "tipo", type: "string", enum: ["admin", "usuario", "vendedor"], example: "admin"),
        new OA\Property(property: "creado_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "actualizado_at", type: "string", format: "date-time", nullable: true)
    ]
)]
#[OA\Schema(
    schema: "Producto",
    type: "object",
    properties: [
        new OA\Property(property: "IdProducto", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Producto Ejemplo"),
        new OA\Property(property: "descripcion", type: "string", example: "Descripción del producto", nullable: true),
        new OA\Property(property: "precio", type: "number", format: "float", example: 99.99),
        new OA\Property(property: "cantidad_disponible", type: "integer", example: 50),
        new OA\Property(property: "categoria", type: "string", example: "Electrónica", nullable: true),
        new OA\Property(property: "proveedor", type: "integer", nullable: true),
        new OA\Property(property: "codigoProducto", type: "string", example: "PROD-001", nullable: true),
        new OA\Property(property: "estado", type: "string", enum: ["disponible", "agotado", "expirado"], example: "disponible"),
        new OA\Property(property: "fecha_creacion", type: "string", format: "date", example: "15/01/2024", description: "Fecha de creación en formato dd/mm/AAAA", nullable: true),
        new OA\Property(property: "fecha_actualizacion", type: "string", format: "date", example: "15/01/2024", description: "Fecha de actualización en formato dd/mm/AAAA", nullable: true)
    ]
)]
#[OA\Schema(
    schema: "Cliente",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "María García"),
        new OA\Property(property: "identificacion", type: "string", example: "123456789"),
        new OA\Property(property: "email", type: "string", example: "maria@ejemplo.com"),
        new OA\Property(property: "telefono", type: "string", example: "+1234567890"),
        new OA\Property(property: "estado", type: "boolean", example: true)
    ]
)]
#[OA\Schema(
    schema: "LoginRequest",
    type: "object",
    required: ["correo", "contraseña"],
    properties: [
        new OA\Property(property: "correo", type: "string", format: "email", example: "admin@sistema.com"),
        new OA\Property(property: "contraseña", type: "string", format: "password", example: "Admin2024!")
    ]
)]
#[OA\Schema(
    schema: "RegisterRequest",
    type: "object",
    required: ["correo", "nombre", "contraseña", "tipo"],
    properties: [
        new OA\Property(property: "correo", type: "string", format: "email", example: "juan@ejemplo.com"),
        new OA\Property(property: "nombre", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "contraseña", type: "string", format: "password", example: "password123"),
        new OA\Property(property: "tipo", type: "string", enum: ["admin", "usuario", "vendedor"], example: "usuario")
    ]
)]
#[OA\Schema(
    schema: "Proveedor",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Proveedor ABC"),
        new OA\Property(property: "direccion", type: "string", example: "Calle 123 #45-67"),
        new OA\Property(property: "telefono", type: "string", example: "3001234567"),
        new OA\Property(property: "estado", type: "boolean", example: true)
    ]
)]
#[OA\Schema(
    schema: "Venta",
    type: "object",
    properties: [
        new OA\Property(property: "id_ventas", type: "integer", example: 1),
        new OA\Property(property: "fecha_venta", type: "string", format: "date", example: "15/01/2024", description: "Fecha de venta en formato dd/mm/AAAA"),
        new OA\Property(property: "total", type: "number", format: "float", example: 150.50),
        new OA\Property(property: "id_cliente", type: "integer", example: 1),
        new OA\Property(property: "cliente", ref: "#/components/schemas/Cliente"),
        new OA\Property(property: "detalles", type: "array", items: new OA\Items(ref: "#/components/schemas/DetalleVenta"))
    ]
)]
#[OA\Schema(
    schema: "DetalleVenta",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "id_venta", type: "integer", example: 1),
        new OA\Property(property: "id_producto", type: "integer", example: 1),
        new OA\Property(property: "cantidad", type: "integer", example: 3),
        new OA\Property(property: "precio", type: "number", format: "float", example: 50.00),
        new OA\Property(property: "producto", ref: "#/components/schemas/Producto")
    ]
)]
#[OA\Schema(
    schema: "Factura",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "numero_facturas", type: "string", example: "F-000001"),
        new OA\Property(property: "fecha", type: "string", format: "date", example: "15/01/2024", description: "Fecha de factura en formato dd/mm/AAAA"),
        new OA\Property(property: "total", type: "number", format: "float", example: 150.50),
        new OA\Property(property: "cliente_id", type: "integer", example: 1),
        new OA\Property(property: "proveedor_id", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "estado", type: "string", enum: ["pendiente", "disponible", "agotado"], example: "pendiente"),
        new OA\Property(property: "cliente", ref: "#/components/schemas/Cliente"),
        new OA\Property(property: "proveedor", ref: "#/components/schemas/Proveedor", nullable: true),
        new OA\Property(property: "detalles", type: "array", items: new OA\Items(ref: "#/components/schemas/DetalleFactura"))
        // MODIFICADO: 2025-11-24 - Eliminado pdf_path del schema (PDF se genera en Angular, no en backend)
    ]
)]
#[OA\Schema(
    schema: "DetalleFactura",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "factura_id", type: "integer", example: 1),
        new OA\Property(property: "producto_id", type: "integer", example: 1),
        new OA\Property(property: "cantidad", type: "integer", example: 3),
        new OA\Property(property: "precio_unitario", type: "number", format: "float", example: 50.00),
        new OA\Property(property: "subtotal", type: "number", format: "float", example: 150.00),
        new OA\Property(property: "producto", ref: "#/components/schemas/Producto")
    ]
)]

class DocumentationSchemas
{
    // Esta clase solo existe para contener las definiciones de esquemas
}

