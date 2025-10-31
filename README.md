# 🏪 Sistema de Gestión de Inventario API

**Sistema completo para gestionar inventario para empresas en crecimiento**

API REST desarrollada con **Laravel** que permite gestionar productos, clientes, proveedores, ventas y facturas de manera eficiente. Incluye documentación API completa con Swagger, generación de PDFs, control de stock automático y reportes detallados.

---

## 📋 Descripción del Repositorio

Sistema de gestión de inventario con API REST documentada. Incluye funcionalidades de CRUD para productos, clientes, proveedores, ventas y facturas, con control automático de stock, generación de PDFs, reportes y autenticación. Ideal para empresas pequeñas y medianas que buscan digitalizar su inventario.

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

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/sistema-gestion-inventario.git
cd sistema-gestion-inventario
```

### 2. Configurar el Backend

```bash
cd inventarioBackend

# Instalar dependencias
composer install

# Copiar archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Generar documentación Swagger
php artisan l5-swagger:generate

# Iniciar servidor de desarrollo
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

**Documentación API (Swagger):** `http://localhost:8000/api/documentation`

### 3. Configurar Variables de Entorno

Edita el archivo `.env` en `inventarioBackend/`:

```env
APP_NAME="Sistema de Gestión de Inventario"
APP_ENV=local
APP_KEY=base64:... (generado automáticamente)
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventarioDB
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 4. Crear Base de Datos PostgreSQL

```sql
CREATE DATABASE inventarioDB;
CREATE USER tu_usuario WITH PASSWORD 'tu_contraseña';
GRANT ALL PRIVILEGES ON DATABASE inventarioDB TO tu_usuario;
```

---

## 📚 Uso de la API

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

Consulta la documentación completa en Swagger: `http://localhost:8000/api/documentation`

---

## 📁 Estructura del Proyecto

```
sistema-gestion-inventario/
│
└── inventarioBackend/          # API Laravel
    ├── app/
    │   ├── Http/
    │   │   └── Controllers/   # Controladores de la API
    │   └── Models/            # Modelos Eloquent
    ├── database/
    │   └── migrations/        # Migraciones de BD
    ├── routes/
    │   └── api.php           # Rutas de la API
    └── storage/
        └── api-docs/          # Documentación Swagger generada
```

---

## 🧪 Testing

```bash
cd inventarioBackend
php artisan test
```

---

## 📄 Documentación

- **API Documentation (Swagger):** Disponible en `/api/documentation` cuando el servidor está corriendo
- **Archivos de documentación adicional:**
  - `inventarioBackend/INSTRUCCIONES-SWAGGER.md` - Guía de Swagger
  - `inventarioBackend/RESUMEN-CORRECCIONES.md` - Correcciones realizadas


---

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo `LICENSE` para más detalles.

---

## 👨‍💻 Autor

- GitHub: [@jesusvp20](https://github.com/jesusvp20)
- LinkedIn: [Jesus Vega]([https://linkedin.com/in/tu-perfil](https://www.linkedin.com/in/jesus-david-vega-pernett-b55880170/))

---

## 🙏 Agradecimientos

- Laravel Framework
- Comunidad Open Source

---

## 📞 Soporte

Si tienes preguntas o necesitas ayuda, puedes:
- Abrir un [Issue](https://github.com/tu-usuario/sistema-gestion-inventario/issues)
- Contactar por email: vegapernettj@gmail.com

---

⭐ **Si este proyecto te fue útil, no olvides darle una estrella!** ⭐

