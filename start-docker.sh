#!/bin/bash
set -e

cd /var/www/html

# Crear archivo .env si no existe (usando variables de entorno de Render)
if [ ! -f .env ]; then
    echo "Creando archivo .env desde variables de entorno..."
    cat > .env <<EOF
APP_NAME=${APP_NAME:-Laravel}
APP_ENV=${APP_ENV:-production}
APP_KEY=
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DATABASE_URL=${DATABASE_URL}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
EOF
fi

# Ejecutar package discovery (necesario para l5-swagger)
composer dump-autoload --optimize || true

# Generar clave de aplicación si no existe
php artisan key:generate --force

# Ejecutar migraciones
php artisan migrate --force

# Generar documentación Swagger
php artisan l5-swagger:generate || echo "Warning: No se pudo generar Swagger, continuando..."

# Cachear configuraciones
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar servidor usando la variable PORT de Render
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

