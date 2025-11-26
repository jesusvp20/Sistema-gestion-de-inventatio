#!/usr/bin/env bash
set -e

echo "🚀 Iniciando build para Render..."

# Cambiar al directorio del backend
cd inventarioBackend

echo "📦 Instalando dependencias de Composer..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "🧹 Limpiando caché antes de regenerar autoloader..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

echo "🔄 Regenerando autoloader de Composer..."
composer dump-autoload --optimize --no-dev

echo "🔑 Generando clave de aplicación..."
php artisan key:generate --force

echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

echo "📚 Generando documentación Swagger..."
if php artisan l5-swagger:generate 2>&1 | tee /tmp/swagger.log; then
    echo "✅ Documentación Swagger generada exitosamente"
else
    echo "⚠️  Warning: No se pudo generar Swagger, continuando..."
    # Mostrar solo errores críticos, ignorar warnings de clases desconocidas
    if grep -q "ErrorException\|FatalError" /tmp/swagger.log; then
        echo "❌ Error crítico en Swagger, revisando logs..."
        cat /tmp/swagger.log
        exit 1
    fi
fi

echo "⚡ Cacheando configuraciones..."
php artisan config:cache
php artisan route:cache

# Intentar cachear vistas, pero no fallar si no hay configuración de vistas
echo "📄 Cacheando vistas (si está disponible)..."
if php artisan view:cache 2>&1; then
    echo "✅ Vistas cacheadas"
else
    echo "⚠️  Warning: No se pudo cachear vistas, continuando..."
fi

echo "✅ Build completado exitosamente!"

