# Swagger Temporalmente Desactivado

**Fecha:** 2025-11-18  
**Razón:** Anotaciones OpenAPI mal formadas en varios controladores

## Problema

Las anotaciones `#[OA\...]` en los siguientes controladores tienen errores de sintaxis:
- VentasController
- ProveedoresController  
- ProductosController (posiblemente)
- ClientesController (posiblemente)

## Estado Actual

- ✅ **API funciona correctamente**
- ✅ Login/Logout funcionan
- ✅ Todos los endpoints operativos
- ❌ Documentación Swagger no se genera

## Endpoints Funcionando

### Autenticación
- POST /api/register
- POST /api/login (correo + password)
- GET /api/user (requiere Bearer token)
- POST /api/logout (requiere Bearer token)

### Usuarios
- GET /api/usuarios
- POST /api/usuarios
- GET /api/usuarios/{id}
- PUT /api/usuarios/{id}
- DELETE /api/usuarios/{id}

### Productos
- GET /api/productos
- POST /api/productos
- Otros endpoints de productos

### Clientes
- GET /api/clientes
- POST /api/clientes
- Otros endpoints de clientes

### Proveedores
- GET /api/proveedores
- POST /api/proveedores
- Otros endpoints de proveedores

### Ventas
- GET /api/ventas
- POST /api/ventas
- Otros endpoints de ventas

### Facturas
- GET /api/gestion-facturas
- Otros endpoints de facturas

## Solución Temporal

Usa **Postman** o **Thunder Client** para probar los endpoints mientras se arregla Swagger.

## Cómo Arreglar (Futuro)

1. Revisar y corregir anotaciones OA en cada controller
2. O eliminar todas las anotaciones y documentar manualmente
3. O usar herramienta externa como Postman Collections

## Prioridad

**BAJA** - La funcionalidad del sistema NO se ve afectada. Swagger es solo documentación.

