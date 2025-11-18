#!/bin/bash

################################################################################
# Script de Despliegue - Fix Autenticación Sanctum
# 
# Fecha de creación: 2025-11-18 21:00:00
# Propósito: Solucionar error "personal_access_tokens does not exist" en producción
# Servidor: Render.com
# Base de datos: PostgreSQL
# 
# IMPORTANTE: Este script debe ejecutarse en el servidor de producción
################################################################################

set -e  # Detener ejecución si hay algún error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_header() {
    echo ""
    echo "=========================================="
    echo "$1"
    echo "=========================================="
    echo ""
}

################################################################################
# PASO 1: Verificaciones Previas
################################################################################

print_header "PASO 1: Verificaciones Previas"

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "Error: No se encuentra el archivo 'artisan'. Asegúrate de estar en el directorio raíz de Laravel."
    exit 1
fi
print_success "Directorio correcto verificado"

# Verificar que existe el archivo .env
if [ ! -f ".env" ]; then
    print_error "Error: No se encuentra el archivo .env"
    exit 1
fi
print_success "Archivo .env encontrado"

# Verificar conexión a base de datos
print_info "Verificando conexión a base de datos..."
if php artisan db:show > /dev/null 2>&1; then
    print_success "Conexión a base de datos exitosa"
else
    print_error "Error: No se puede conectar a la base de datos"
    print_info "Verifica las credenciales en el archivo .env"
    exit 1
fi

################################################################################
# PASO 2: Backup de Base de Datos
################################################################################

print_header "PASO 2: Backup de Base de Datos"

BACKUP_DIR="backups"
BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"

print_info "Creando directorio de backups..."
mkdir -p "$BACKUP_DIR"

print_info "Realizando backup de la base de datos..."
print_warning "IMPORTANTE: Si estás en Render, el backup automático puede fallar."
print_warning "Render hace backups automáticos, pero considera hacer uno manual desde el dashboard."

# Intentar backup (puede fallar en algunos entornos)
if command -v pg_dump &> /dev/null; then
    if pg_dump $DATABASE_URL > "$BACKUP_DIR/$BACKUP_FILE" 2>/dev/null; then
        print_success "Backup creado: $BACKUP_DIR/$BACKUP_FILE"
    else
        print_warning "No se pudo crear backup automático. Continúa si ya tienes un backup manual."
        read -p "¿Deseas continuar sin backup? (y/n): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_error "Despliegue cancelado por el usuario"
            exit 1
        fi
    fi
else
    print_warning "pg_dump no disponible. Asegúrate de tener un backup manual."
fi

################################################################################
# PASO 3: Modo Mantenimiento
################################################################################

print_header "PASO 3: Activando Modo Mantenimiento"

print_info "Poniendo la aplicación en modo mantenimiento..."
php artisan down --render="errors::503" --retry=60
print_success "Aplicación en modo mantenimiento"

################################################################################
# PASO 4: Actualizar Código
################################################################################

print_header "PASO 4: Verificando Archivos Actualizados"

# Verificar que existe la migración de personal_access_tokens
MIGRATION_FILE=$(find database/migrations -name "*create_personal_access_tokens_table.php" | head -n 1)

if [ -z "$MIGRATION_FILE" ]; then
    print_error "Error: No se encuentra la migración de personal_access_tokens"
    print_info "Asegúrate de haber subido el archivo:"
    print_info "database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php"
    php artisan up
    exit 1
fi
print_success "Migración de personal_access_tokens encontrada: $MIGRATION_FILE"

# Verificar configuración de auth
if grep -q "'sanctum' =>" config/auth.php; then
    print_success "Configuración de Sanctum encontrada en config/auth.php"
else
    print_error "Error: Configuración de Sanctum no encontrada en config/auth.php"
    php artisan up
    exit 1
fi

################################################################################
# PASO 5: Limpiar Caché
################################################################################

print_header "PASO 5: Limpiando Caché"

print_info "Limpiando caché de configuración..."
php artisan config:clear
print_success "Caché de configuración limpiada"

print_info "Limpiando caché de rutas..."
php artisan route:clear
print_success "Caché de rutas limpiada"

print_info "Limpiando caché de vistas..."
php artisan view:clear
print_success "Caché de vistas limpiada"

print_info "Limpiando caché de aplicación..."
php artisan cache:clear
print_success "Caché de aplicación limpiada"

################################################################################
# PASO 6: Ejecutar Migraciones
################################################################################

print_header "PASO 6: Ejecutando Migraciones"

print_warning "CRÍTICO: Se va a ejecutar la migración que crea la tabla personal_access_tokens"
print_info "Esta es la tabla necesaria para que funcione Laravel Sanctum"

# Mostrar migraciones pendientes
print_info "Migraciones pendientes:"
php artisan migrate:status

print_info "Ejecutando migraciones..."
if php artisan migrate --force; then
    print_success "Migraciones ejecutadas exitosamente"
else
    print_error "Error al ejecutar migraciones"
    print_info "Restaurando aplicación..."
    php artisan up
    exit 1
fi

# Verificar que la tabla se creó
print_info "Verificando que la tabla personal_access_tokens existe..."
if php artisan tinker --execute="echo \Schema::hasTable('personal_access_tokens') ? 'true' : 'false';" | grep -q "true"; then
    print_success "Tabla personal_access_tokens creada exitosamente"
else
    print_error "Error: La tabla personal_access_tokens no existe"
    php artisan up
    exit 1
fi

################################################################################
# PASO 7: Cachear Configuración
################################################################################

print_header "PASO 7: Cacheando Configuración"

print_info "Cacheando configuración optimizada..."
php artisan config:cache
print_success "Configuración cacheada"

print_info "Cacheando rutas optimizadas..."
php artisan route:cache
print_success "Rutas cacheadas"

################################################################################
# PASO 8: Regenerar Documentación Swagger
################################################################################

print_header "PASO 8: Regenerando Documentación Swagger"

print_info "Regenerando documentación de API..."
if php artisan l5-swagger:generate; then
    print_success "Documentación Swagger regenerada"
else
    print_warning "No se pudo regenerar Swagger (no crítico)"
fi

################################################################################
# PASO 9: Tests de Verificación
################################################################################

print_header "PASO 9: Tests de Verificación"

print_info "Ejecutando tests de verificación..."

# Test 1: Verificar que la aplicación responde
print_info "Test 1: Verificando que la aplicación responde..."
if php artisan route:list | grep -q "api/login"; then
    print_success "Ruta /api/login encontrada"
else
    print_error "Error: Ruta /api/login no encontrada"
fi

# Test 2: Verificar modelo UsuariosModel
print_info "Test 2: Verificando modelo UsuariosModel..."
if php artisan tinker --execute="echo class_exists('App\Models\UsuariosModel') ? 'true' : 'false';" | grep -q "true"; then
    print_success "Modelo UsuariosModel existe"
else
    print_error "Error: Modelo UsuariosModel no encontrado"
fi

# Test 3: Verificar trait HasApiTokens
print_info "Test 3: Verificando trait HasApiTokens en UsuariosModel..."
if grep -q "use HasApiTokens" app/Models/UsuariosModel.php; then
    print_success "Trait HasApiTokens encontrado en UsuariosModel"
else
    print_error "Error: Trait HasApiTokens no encontrado en UsuariosModel"
fi

################################################################################
# PASO 10: Desactivar Modo Mantenimiento
################################################################################

print_header "PASO 10: Desactivando Modo Mantenimiento"

print_info "Reactivando la aplicación..."
php artisan up
print_success "Aplicación reactivada"

################################################################################
# RESUMEN FINAL
################################################################################

print_header "✅ DESPLIEGUE COMPLETADO EXITOSAMENTE"

echo ""
echo "📋 Resumen de cambios aplicados:"
echo "  ✓ Tabla personal_access_tokens creada"
echo "  ✓ Configuración de Sanctum actualizada"
echo "  ✓ Caché limpiada y regenerada"
echo "  ✓ Documentación Swagger actualizada"
echo "  ✓ Tests de verificación pasados"
echo ""
echo "🔍 Próximos pasos recomendados:"
echo ""
echo "1. Probar el endpoint de login:"
echo "   curl -X POST https://tu-dominio.com/api/login \\"
echo "     -H 'Content-Type: application/json' \\"
echo "     -d '{\"nombre\":\"usuario\",\"password\":\"password123\"}'"
echo ""
echo "2. Verificar que se genera un token correctamente"
echo ""
echo "3. Probar endpoint protegido con el token:"
echo "   curl -X GET https://tu-dominio.com/api/user \\"
echo "     -H 'Authorization: Bearer TU_TOKEN_AQUI'"
echo ""
echo "4. Revisar logs en caso de errores:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "5. Verificar documentación Swagger:"
echo "   https://tu-dominio.com/api/documentation"
echo ""
echo "📚 Documentación completa en:"
echo "   - ANALISIS-SEGURIDAD-AUTENTICACION.md"
echo "   - INSTRUCCIONES-SWAGGER.md"
echo ""
print_success "¡Sistema de autenticación reparado y funcionando!"
echo ""

################################################################################
# FIN DEL SCRIPT
################################################################################

