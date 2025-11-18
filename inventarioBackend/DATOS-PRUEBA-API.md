# Datos de Prueba - API Sistema de Gestión de Inventario

**Fecha:** 2025-11-18  
**Servidor Local:** http://localhost:8000/api  
**Servidor Producción:** https://sistema-gestion-de-inventatio.onrender.com/api

---

## AUTENTICACION

### 1. Registrar Usuario de Prueba

**Endpoint:** POST /register

**Request Body:**
```json
{
  "correo": "admin@prueba.com",
  "nombre": "admin_prueba",
  "contraseña": "Admin123!",
  "tipo": "admin"
}
```

**Respuesta Esperada:** Status 201
```json
{
  "status": "success",
  "message": "Usuario creado exitosamente",
  "data": {
    "correo": "admin@prueba.com",
    "nombre": "admin_prueba",
    "tipo": "admin",
    "id": 1
  },
  "statusCode": 201
}
```

---

### 2. Iniciar Sesión

**Endpoint:** POST /login

**Request Body:**
```json
{
  "nombre": "admin_prueba",
  "password": "Admin123!"
}
```

**Respuesta Esperada:** Status 200
```json
{
  "status": "success",
  "message": "Inicio de sesión exitoso",
  "data": {
    "usuario": {
      "id": 1,
      "nombre": "admin_prueba",
      "correo": "admin@prueba.com",
      "tipo": "admin"
    },
    "token": "1|abcdef123456..."
  },
  "statusCode": 200
}
```

**IMPORTANTE:** Guarda el token para usarlo en los siguientes endpoints

---

### 3. Obtener Usuario Autenticado

**Endpoint:** GET /user

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
```

**Respuesta Esperada:** Status 200
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "correo": "admin@prueba.com",
    "nombre": "admin_prueba",
    "tipo": "admin"
  },
  "statusCode": 200
}
```

---

### 4. Cerrar Sesión

**Endpoint:** POST /logout

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
```

**Respuesta Esperada:** Status 200
```json
{
  "status": "success",
  "message": "Sesión cerrada exitosamente",
  "statusCode": 200
}
```

---

## USUARIOS ADICIONALES DE PRUEBA

### Usuario Vendedor
```json
{
  "correo": "vendedor@prueba.com",
  "nombre": "vendedor_test",
  "contraseña": "Vendedor123!",
  "tipo": "vendedor"
}
```

### Usuario Normal
```json
{
  "correo": "usuario@prueba.com",
  "nombre": "usuario_test",
  "contraseña": "Usuario123!",
  "tipo": "usuario"
}
```

---

## CLIENTES

### 1. Crear Cliente

**Endpoint:** POST /clientes

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
Content-Type: application/json
```

**Request Body:**
```json
{
  "nombre": "Juan Pérez García",
  "correo": "juan.perez@email.com",
  "telefono": "555-0123",
  "direccion": "Av. Principal 123, Col. Centro",
  "estado": "activo"
}
```

### 2. Listar Clientes

**Endpoint:** GET /clientes

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
```

### 3. Buscar Cliente por Nombre

**Endpoint:** GET /clientes/buscar?nombre=Juan

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
```

### 4. Listar Clientes Activos

**Endpoint:** GET /clientes/activos

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
```

---

## PRODUCTOS

### 1. Crear Producto

**Endpoint:** POST /productos

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
Content-Type: application/json
```

**Request Body:**
```json
{
  "codigoProducto": "PROD-001",
  "nombre": "Laptop Dell Inspiron 15",
  "descripcion": "Laptop 15.6 pulgadas, Intel i5, 8GB RAM, 256GB SSD",
  "precio": 12999.99,
  "cantidad": 25,
  "categoria": "Electrónica"
}
```

### 2. Más Productos de Prueba

**Producto 2:**
```json
{
  "codigoProducto": "PROD-002",
  "nombre": "Mouse Logitech MX Master 3",
  "descripcion": "Mouse inalámbrico ergonómico",
  "precio": 1299.00,
  "cantidad": 50,
  "categoria": "Accesorios"
}
```

**Producto 3:**
```json
{
  "codigoProducto": "PROD-003",
  "nombre": "Teclado Mecánico RGB",
  "descripcion": "Teclado mecánico con iluminación RGB",
  "precio": 899.00,
  "cantidad": 30,
  "categoria": "Accesorios"
}
```

### 3. Listar Productos

**Endpoint:** GET /productos

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
```

### 4. Buscar Producto por Código

**Endpoint:** GET /productos/buscar?codigo=PROD-001

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
```

---

## PROVEEDORES

### 1. Crear Proveedor

**Endpoint:** POST /proveedores

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
Content-Type: application/json
```

**Request Body:**
```json
{
  "nombre": "Distribuidora Tech SA de CV",
  "correo": "ventas@distribuidoratech.com",
  "telefono": "555-9876",
  "direccion": "Calle Industria 456, Parque Industrial",
  "estado": "activo"
}
```

### 2. Más Proveedores de Prueba

**Proveedor 2:**
```json
{
  "nombre": "Importadora Global",
  "correo": "contacto@importadoraglobal.com",
  "telefono": "555-5555",
  "direccion": "Av. Comercio 789",
  "estado": "activo"
}
```

---

## FACTURAS

### 1. Crear Factura

**Endpoint:** POST /facturas

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
Content-Type: application/json
```

**Request Body:**
```json
{
  "cliente_id": 1,
  "proveedor_id": 1,
  "fecha": "2025-11-18",
  "total": 15000.00,
  "estado": "pendiente"
}
```

---

## VENTAS

### 1. Crear Venta

**Endpoint:** POST /ventas

**Headers:**
```
Authorization: Bearer {TU_TOKEN_AQUI}
Content-Type: application/json
```

**Request Body:**
```json
{
  "cliente_id": 1,
  "producto_id": 1,
  "cantidad": 2,
  "precio_unitario": 12999.99,
  "total": 25999.98,
  "fecha": "2025-11-18"
}
```

---

## COMANDOS CURL COMPLETOS

### Registro
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "correo": "admin@prueba.com",
    "nombre": "admin_prueba",
    "contraseña": "Admin123!",
    "tipo": "admin"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "admin_prueba",
    "password": "Admin123!"
  }'
```

### Obtener Usuario (reemplaza TOKEN con el token obtenido)
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer TOKEN"
```

### Crear Cliente
```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Pérez García",
    "correo": "juan.perez@email.com",
    "telefono": "555-0123",
    "direccion": "Av. Principal 123, Col. Centro",
    "estado": "activo"
  }'
```

### Crear Producto
```bash
curl -X POST http://localhost:8000/api/productos \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigoProducto": "PROD-001",
    "nombre": "Laptop Dell Inspiron 15",
    "descripcion": "Laptop 15.6 pulgadas",
    "precio": 12999.99,
    "cantidad": 25,
    "categoria": "Electrónica"
  }'
```

---

## POSTMAN COLLECTION

### Variables de Entorno
```
base_url: http://localhost:8000/api
token: (se actualiza después del login)
```

### Secuencia de Prueba Recomendada

1. POST /register (crear usuario admin)
2. POST /login (obtener token)
3. GET /user (verificar autenticación)
4. POST /clientes (crear cliente de prueba)
5. POST /productos (crear producto de prueba)
6. POST /proveedores (crear proveedor de prueba)
7. POST /ventas (crear venta de prueba)
8. POST /logout (cerrar sesión)

---

## CASOS DE PRUEBA - VALIDACIONES

### Login con Credenciales Incorrectas
```json
{
  "nombre": "usuario_inexistente",
  "password": "wrongpassword"
}
```
**Esperado:** Status 401

### Registro con Email Duplicado
```json
{
  "correo": "admin@prueba.com",
  "nombre": "otro_usuario",
  "contraseña": "Password123!",
  "tipo": "usuario"
}
```
**Esperado:** Status 400 (email ya existe)

### Acceso sin Token
```bash
curl -X GET http://localhost:8000/api/user
```
**Esperado:** Status 401

### Token Inválido
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer token_invalido_123"
```
**Esperado:** Status 401

---

## DATOS DE PRUEBA COMPLETOS (SEEDER)

Si quieres cargar datos de prueba automáticamente, crea un seeder:

```php
// database/seeders/TestDataSeeder.php

public function run()
{
    // Usuario Admin
    UsuariosModel::create([
        'correo' => 'admin@prueba.com',
        'nombre' => 'admin_prueba',
        'contraseña' => Hash::make('Admin123!'),
        'tipo' => 'admin'
    ]);

    // Cliente
    ClientesModel::create([
        'nombre' => 'Juan Pérez García',
        'correo' => 'juan.perez@email.com',
        'telefono' => '555-0123',
        'direccion' => 'Av. Principal 123',
        'estado' => 'activo'
    ]);

    // Producto
    ProductosModel::create([
        'codigoProducto' => 'PROD-001',
        'nombre' => 'Laptop Dell Inspiron 15',
        'descripcion' => 'Laptop 15.6 pulgadas',
        'precio' => 12999.99,
        'cantidad' => 25,
        'categoria' => 'Electrónica'
    ]);
}
```

Ejecutar: `php artisan db:seed --class=TestDataSeeder`

---

## NOTAS IMPORTANTES

1. **Tokens:** Los tokens de Sanctum no expiran por defecto. Considera configurar expiración.
2. **Rate Limiting:** Actualmente no hay límite de intentos de login. Implementar throttle.
3. **HTTPS:** En producción, siempre usa HTTPS para proteger los tokens.
4. **Logs:** Revisa `storage/logs/laravel.log` si hay errores.

---

**Última actualización:** 2025-11-18  
**Versión:** 1.0.0

