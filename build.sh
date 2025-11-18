#!/usr/bin/env bash
set -e

echo "🚀 Iniciando build para Render..."

# Cambiar al directorio del backend
cd inventarioBackend

echo "📦 Instalando dependencias de Composer..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "🔑 Generando clave de aplicación..."
php artisan key:generate --force

echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

echo "📚 Generando documentación Swagger..."
php artisan l5-swagger:generate

echo "⚡ Cacheando configuraciones..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Build completado exitosamente!"

