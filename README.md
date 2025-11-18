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

## 🚀 Despliegue

### Desplegar en Railway

1. **Conectar repositorio:**
   - Ve a [railway.app](https://railway.app) y crea una cuenta
   - Crea un nuevo proyecto → "Deploy from GitHub repo"
   - Selecciona tu repositorio

2. **Configurar base de datos PostgreSQL:**
   - En Railway, agrega un servicio PostgreSQL
   - Railway te dará automáticamente las variables de entorno:
     - `DATABASE_URL` (o `PGHOST`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`, `PGPORT`)

3. **Configurar variables de entorno:**
   En Railway, agrega estas variables en tu servicio:
   ```
   APP_NAME=Sistema de Gestión de Inventario
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=(generar con: php artisan key:generate --show)
   DB_CONNECTION=pgsql
   DB_HOST=${{Postgres.PGHOST}}
   DB_DATABASE=${{Postgres.PGDATABASE}}
   DB_USERNAME=${{Postgres.PGUSER}}
   DB_PASSWORD=${{Postgres.PGPASSWORD}}
   DB_PORT=${{Postgres.PGPORT}}
   ```

4. **Configurar Root Directory (importante):**
   - En Railway, ve a Settings → Root Directory
   - Establece: `inventarioBackend`
   - O deja la raíz y Railway usará los archivos `railway.json` y `start.sh` creados

5. **Desplegar:**
   - Railway detectará automáticamente PHP y ejecutará el build
   - El archivo `start.sh` ejecutará las migraciones y servirá la aplicación

### Desplegar en Render (con Docker)

1. **Crear base de datos PostgreSQL:**
   - Ve a [render.com](https://render.com) y crea una cuenta
   - Crea un nuevo servicio → "PostgreSQL"
   - Copia la **URL de la base de datos interna** (Internal Database URL)
   - Ejemplo: `postgresql://usuario:password@host/database`

2. **Crear servicio Web:**
   - Crea un nuevo servicio → "Web Service"
   - Conecta tu repositorio de GitHub
   - Selecciona el repositorio y la rama

3. **Configurar el servicio:**
   - **Environment:** `Docker` (selecciona Docker de la lista)
   - **Build Command:** (dejar vacío - Render usará el Dockerfile automáticamente)
   - **Start Command:** (dejar vacío - el Dockerfile ya tiene el comando configurado)
   - **Root Directory:** (dejar vacío, la raíz del repositorio)

4. **Configurar variables de entorno:**
   En la sección "Environment Variables" de Render, agrega:
   ```
   APP_NAME=Sistema de Gestión de Inventario
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=pgsql
   DATABASE_URL=postgresql://inventariodb_m4cg_user:IPioEijXgdmr4ryLA4V2rN6IMevjG7cl@dpg-d4eb873e5dus73fd4qf0-a/inventariodb_m4cg
   ```
   
   **Nota:** 
   - Usa la **URL interna** de tu base de datos PostgreSQL
   - `APP_KEY` se generará automáticamente durante el build, no es necesario configurarlo manualmente
   - `PORT` se configura automáticamente por Render, no necesitas agregarla

5. **Desplegar:**
   - Render detectará automáticamente el `Dockerfile` y construirá la imagen
   - El script `start-docker.sh` ejecutará migraciones y optimizará la aplicación
   - Tu API estará disponible en la URL proporcionada por Render

**Archivos importantes para Render con Docker:**
- `Dockerfile` - Configuración de la imagen Docker con PHP 8.2
- `start-docker.sh` - Script que ejecuta migraciones y inicia el servidor
- `.dockerignore` - Archivos excluidos del build de Docker
- `inventarioBackend/app/Providers/AppServiceProvider.php` - Configurado para forzar HTTPS en producción

### Opciones de Hosting Gratuito

1. **Railway** - [railway.app](https://railway.app) ⭐ Recomendado
2. **Render** - [render.com](https://render.com) ⭐ Con Docker
3. **Fly.io** - [fly.io](https://fly.io)

### Configuración para Producción

1. Cambia `APP_ENV=production` y `APP_DEBUG=false` en `.env`
2. Ejecuta `php artisan config:cache`
3. Ejecuta `php artisan route:cache`
4. Configura la conexión a PostgreSQL en producción
5. Actualiza `APP_URL` con la URL de producción

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Para cambios importantes:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo `LICENSE` para más detalles.

---

## 👨‍💻 Autor

**Tu Nombre**
- GitHub: [@tu-usuario](https://github.com/tu-usuario)
- LinkedIn: [Tu Perfil](https://linkedin.com/in/tu-perfil)

---

## 🙏 Agradecimientos

- Laravel Framework
- Comunidad Open Source

---

## 📞 Soporte

Si tienes preguntas o necesitas ayuda, puedes:
- Abrir un [Issue](https://github.com/tu-usuario/sistema-gestion-inventario/issues)
- Contactar por email: tu-email@ejemplo.com

---

⭐ **Si este proyecto te fue útil, no olvides darle una estrella!** ⭐

