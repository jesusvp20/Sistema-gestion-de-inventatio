<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Sistema de Gestión de Inventario API",
    description: "Sistema para gestionar inventario para empresas en crecimiento",
    contact: new OA\Contact(
        name: "API Support",
        email: "support@inventario.com"
    )
)]
#[OA\Server(
    url: "/api",
    description: "API Base URL"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "bearerAuth",
    description: "Enter your bearer token",
    bearerFormat: "JWT",
    scheme: "bearer"
)]
#[OA\Tag(
    name: "Usuarios",
    description: "Gestión de usuarios del sistema"
)]
#[OA\Tag(
    name: "Productos",
    description: "Gestión de productos"
)]
#[OA\Tag(
    name: "Clientes",
    description: "Gestión de clientes"
)]
#[OA\Tag(
    name: "Facturas",
    description: "Gestión de facturas"
)]
#[OA\Tag(
    name: "Ventas",
    description: "Gestión de ventas"
)]
#[OA\Tag(
    name: "Proveedores",
    description: "Gestión de proveedores"
)]
#[OA\Tag(
    name: "Reportes",
    description: "Reportes y consultas del sistema"
)]
#[OA\Tag(
    name: "Autenticación",
    description: "Autenticación y registro de usuarios"
)]
abstract class Controller
{
    //
}
