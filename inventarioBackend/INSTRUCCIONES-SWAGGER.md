# 📚 Documentación API con Swagger

## ✅ Estado

La documentación API ha sido configurada y generada exitosamente usando **L5-Swagger**.

## 🌐 Acceder a la documentación

Una vez que el servidor Laravel esté ejecutándose, puedes acceder a la documentación interactiva en:

```
http://localhost:8000/api/documentation
```

## 📝 Endpoints Documentados

### Productos
- `GET /productos` - Listar todos los productos
- `POST /productos` - Crear producto
- `GET /productos/{id}` - Mostrar producto específico
- `PUT /productos/{id}` - Actualizar producto
- `DELETE /productos/{id}` - Eliminar producto
- `PATCH /productos/{id}/cambiar-estado` - Cambiar estado del producto
- `GET /productos/buscar` - Buscar productos por nombre
- `GET /productos/activos` - Listar productos activos
- `GET /productos/ordenar` - Ordenar productos por precio

### Clientes
- `GET /clientes` - Listar todos los clientes
- `POST /clientes` - Crear cliente
- `PUT /clientes/{id}` - Actualizar cliente
- `DELETE /clientes/{id}` - Eliminar cliente
- `PATCH /clientes/{id}/estado` - Cambiar estado del cliente
- `GET /clientes/buscar` - Buscar clientes por nombre
- `GET /clientes/activos` - Listar clientes activos

### Proveedores
- `GET /proveedores` - Listar todos los proveedores
- `POST /proveedores` - Crear proveedor
- `PUT /proveedores/{id}` - Actualizar proveedor
- `DELETE /proveedores/{id}` - Eliminar proveedor
- `PATCH /proveedores/{id}/estado` - Cambiar estado del proveedor
- `GET /proveedores/buscar` - Buscar proveedores por nombre
- `GET /proveedores/activos` - Listar proveedores activos

### Ventas
- `GET /ventas` - Listar ventas (con filtros opcionales)
- `POST /ventas` - Crear venta
- `GET /ventas/historial` - Historial de ventas
- `PUT /ventas/{id}` - Actualizar venta
- `DELETE /ventas/{id}` - Eliminar venta

### Facturas
- `GET /facturas` - Listar facturas
- `POST /facturas` - Crear factura
- `GET /facturas/{id}` - Mostrar factura específica
- `POST /facturas/{id}/detalles` - Agregar detalle a factura
- `PUT /facturas/detalles/{id}` - Actualizar detalle de factura
- `DELETE /facturas/detalles/{id}` - Eliminar detalle de factura
- `GET /facturas/proximo-numero` - Obtener próximo número de factura
- `POST /facturas/validar-detalles` - Validar detalles de factura

### Reportes
- `GET /reportes/ventas` - Generar reporte de ventas por periodo

## 🔧 Configuración

### Instalado
- ✅ L5-Swagger v9.0.1
- ✅ Laravel Sanctum v4.2.0
- ✅ Swagger UI v5.30.0

### Archivos importantes

1. **Controller.php** - Configuración principal de Swagger
   - Información de la API
   - Tags (Productos, Clientes, Proveedores, Facturas, Ventas, Reportes, Autenticación)
   - Configuración de seguridad Bearer Token

2. **DocumentationSchemas.php** - Esquemas de datos
   - Schema Usuario
   - Schema Producto
   - Schema Cliente
   - Schema Proveedor
   - Schema Venta
   - Schema DetalleVenta
   - Schema Factura
   - Schema DetalleFactura
   - LoginRequest
   - RegisterRequest

3. **Controladores Documentados**:
   - **productosController.php** - Gestión de productos
   - **ClientesController.php** - Gestión de clientes
   - **proveedoresController.php** - Gestión de proveedores
   - **ventasController.php** - Gestión de ventas
   - **DetalleVentasController.php** - Reportes de ventas
   - **DetalleFacturaController.php** - Gestión de facturas
   - Todos con anotaciones Swagger completas, request/response examples y códigos de estado HTTP

## 🔄 Regenerar documentación

Si agregas o modificas anotaciones Swagger, regenera la documentación con:

```bash
php artisan l5-swagger:generate
```

## 📋 Agregar documentación a más controladores

Para documentar un endpoint, agrega anotaciones antes del método:

```php
#[
    OA\Get(
        path: "/productos",
        summary: "Listar productos",
        description: "Obtiene todos los productos",
        tags: ["Productos"]
    ),
    OA\Response(
        response: 200,
        description: "Lista de productos",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Producto"))
            ]
        )
    )
]
public function index()
{
    // tu código...
}
```

## 🔐 Autenticación

La documentación incluye soporte para **Bearer Token** (Laravel Sanctum).

Para probar endpoints protegidos:

1. Primero, obtén un token con `/login`
2. Luego, en Swagger UI, haz clic en el botón "Authorize"
3. Ingresa el token en el formato: `Bearer {tu-token}`

## 📖 Más información

- [L5-Swagger Documentation](https://github.com/darkaonline/l5-swagger)
- [OpenAPI Specification](https://swagger.io/specification/)

