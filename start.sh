#!/bin/bash
cd inventarioBackend

# Generar clave de aplicación si no existe
php artisan key:generate --force

# Ejecutar migraciones
php artisan migrate --force

# Generar documentación Swagger
php artisan l5-swagger:generate

# Cachear configuraciones
php artisan config:cache
php artisan route:cache

# Iniciar servidor
php artisan serve --host=0.0.0.0 --port=$PORT

