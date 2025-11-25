# 🏪 Sistema de Gestión de Inventario API - Backend

**API REST desarrollada con Laravel para gestión de inventario**

Este es el backend del sistema de gestión de inventario. Proporciona una API REST completa con documentación Swagger, control de stock automático, generación de PDFs y sistema de autenticación.

---

## ✨ Características

- ✅ **API REST completa** con documentación Swagger/OpenAPI
- ✅ **CRUD completo** para productos, clientes, proveedores, ventas y facturas
- ✅ **Control automático de stock** al realizar ventas/facturas
- ✅ **Generación de PDFs** para facturas (DomPDF)
- ✅ **Sistema de autenticación** con Laravel Sanctum
- ✅ **Reportes de ventas** con filtros por fecha
- ✅ **Validaciones robustas** y manejo de transacciones
- ✅ **Base de datos PostgreSQL** para almacenamiento de datos

---

## 🛠️ Tecnologías Utilizadas

- **Laravel 12** - Framework PHP
- **PHP 8.2+** - Lenguaje de programación
- **PostgreSQL** - Base de datos
- **Laravel Sanctum** - Autenticación API
- **l5-swagger** - Documentación OpenAPI/Swagger
- **DomPDF** - Generación de PDFs

---

## 📦 Requisitos Previos

- **PHP 8.2** o superior
- **Composer** - Gestor de dependencias PHP
- **PostgreSQL** 12+ - Base de datos
- **Git** - Control de versiones

---

## 🚀 Instalación

### 1. Instalar dependencias

```bash
composer install
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env`:

```env
APP_NAME="Sistema de Gestión de Inventario"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventarioDB
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 3. Crear base de datos PostgreSQL

```sql
CREATE DATABASE inventarioDB;
CREATE USER tu_usuario WITH PASSWORD 'tu_contraseña';
GRANT ALL PRIVILEGES ON DATABASE inventarioDB TO tu_usuario;
```

### 4. Ejecutar migraciones

```bash
php artisan migrate
```

### 5. Generar documentación Swagger

```bash
php artisan l5-swagger:generate
```

### 6. Iniciar servidor

```bash
php artisan serve
```

La API estará disponible en: `127.0.0.1:8000`

**Documentación API (Swagger):** `127.0.0.1:8000/api/documentation/`

---

##  Uso de la API

### Autenticación

1. **Registrar usuario:**
```bash
POST /api/register
Content-Type: application/json

{
  "nombre": "Juan Pérez",
  "correo": "juan@ejemplo.com",
  "contraseña": "password123",
  "tipo": "admin"
}
```

2. **Iniciar sesión:**
```bash
POST /api/login
Content-Type: application/json

{
  "nombre": "juan@ejemplo.com",
  "password": "password123"
}
```

3. **Usar el token** en headers:
```
Authorization: Bearer {token}
```

### Endpoints Principales

- **Productos:** `/api/productos`
- **Clientes:** `/api/clientes`
- **Proveedores:** `/api/proveedores`
- **Ventas:** `/api/ventas`
- **Facturas:** `/api/facturas`
- **Reportes:** `/api/reportes/ventas`

Consulta la documentación completa en Swagger: `127.0.0.1:8000/api/documentation/`

---

##  Testing

```bash
php artisan test
```

---

## Documentación

- **API Documentation (Swagger):** Disponible en `/api/documentation` cuando el servidor está corriendo
- **Datos de Prueba:** Ver `DATOS-PRUEBA-API.md` para ejemplos de uso de la API

---

##  Despliegue

### Configuración para Producción

1. Cambia `APP_ENV=production` y `APP_DEBUG=false` en `.env`
2. Ejecuta `php artisan config:cache`
3. Ejecuta `php artisan route:cache`
4. Configura la conexión a PostgreSQL en producción
5. Actualiza `APP_URL` con la URL de producción

---

##  Licencia

Este proyecto está bajo la Licencia MIT.
