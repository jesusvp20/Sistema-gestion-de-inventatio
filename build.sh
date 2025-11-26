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

echo "🔄 Regenerando autoloader de Composer (forzando regeneración completa)..."
# Eliminar archivos de autoloader para forzar regeneración completa
rm -f vendor/composer/autoload_classmap.php vendor/composer/autoload_static.php vendor/composer/autoload_psr4.php 2>/dev/null || true
# Regenerar autoloader con optimización pero sin classmap-authoritative para evitar problemas con namespaces
composer dump-autoload --optimize --no-dev

echo "🔑 Generando clave de aplicación..."
php artisan key:generate --force

echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

echo "📚 Generando documentación Swagger..."
# Limpiar caché de Swagger antes de regenerar
rm -f storage/api-docs/api-docs.json storage/api-docs/api-docs.yaml 2>/dev/null || true
php artisan l5-swagger:generate 2>&1 | tee /tmp/swagger.log
SWAGGER_EXIT_CODE=${PIPESTATUS[0]}

if [ $SWAGGER_EXIT_CODE -eq 0 ]; then
    echo "✅ Documentación Swagger generada exitosamente"
else
    echo "⚠️  Warning: Swagger generó warnings, revisando..."
    # Filtrar warnings conocidos de clases desconocidas (pueden ser falsos positivos)
    if grep -q "Skipping unknown" /tmp/swagger.log; then
        echo "⚠️  Advertencia: Se encontraron clases 'unknown' (puede ser problema de autoloader)"
        echo "   Verificando si es crítico..."
    fi
    # Solo fallar si hay errores críticos
    if grep -q "ErrorException\|FatalError\|ParseError" /tmp/swagger.log; then
        echo "❌ Error crítico en Swagger:"
        cat /tmp/swagger.log
        exit 1
    else
        echo "✅ Swagger completado con advertencias menores, continuando..."
    fi
fi

echo "⚡ Cacheando configuraciones..."
php artisan config:cache
php artisan route:cache

# Intentar cachear vistas solo si existe configuración de vistas
# En Laravel 11, si no se usan vistas Blade, puede no haber configuración
echo "📄 Verificando si es necesario cachear vistas..."
VIEW_CACHE_OUTPUT=$(php artisan view:cache 2>&1) || VIEW_CACHE_EXIT=$?
if [ -n "$VIEW_CACHE_OUTPUT" ]; then
    if echo "$VIEW_CACHE_OUTPUT" | grep -q "View path not found"; then
        echo "ℹ️  No hay configuración de vistas, omitiendo cache de vistas..."
    elif [ ${VIEW_CACHE_EXIT:-0} -eq 0 ]; then
        echo "✅ Vistas cacheadas exitosamente"
    else
        echo "⚠️  Warning: No se pudo cachear vistas, continuando..."
        echo "   Detalles: $VIEW_CACHE_OUTPUT"
    fi
else
    echo "✅ Vistas cacheadas exitosamente"
fi

echo "✅ Build completado exitosamente!"

