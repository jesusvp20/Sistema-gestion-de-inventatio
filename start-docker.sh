#!/bin/bash
set -e

cd /var/www/html

# Generar clave de aplicación si no existe
php artisan key:generate --force

# Ejecutar migraciones
php artisan migrate --force

# Generar documentación Swagger
php artisan l5-swagger:generate

# Cachear configuraciones
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar servidor usando la variable PORT de Render
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

