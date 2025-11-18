# INSTRUCCIONES URGENTES - Ejecutar en Render

**PROBLEMA ACTUAL:** Error 500 en todos los endpoints de autenticacion

**CAUSA:** Falta ejecutar la migracion de la tabla personal_access_tokens

---

## PASOS A SEGUIR EN RENDER (URGENTE)

### 1. Acceder a la Shell de Render

1. Ve a: https://dashboard.render.com
2. Selecciona tu servicio: **sistema-gestion-de-inventatio**
3. En el menu lateral izquierdo, haz clic en **"Shell"**
4. Espera a que se abra la terminal

---

### 2. Ejecutar Estos Comandos (Copiar y Pegar)

```bash
# Verificar directorio actual
pwd

# Ejecutar migraciones (CRITICO)
php artisan migrate --force

# Crear usuario administrador
php artisan db:seed --class=AdminUserSeeder --force

# Limpiar cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Cachear configuracion optimizada
php artisan config:cache
php artisan route:cache

# Regenerar Swagger
php artisan l5-swagger:generate
```

**IMPORTANTE:** El sistema creara automaticamente el usuario admin:
- **Correo:** admin@sistema.com
- **Contraseña:** Admin2024!

(Cambia la contraseña despues del primer login)

---

### 3. Verificar que Funciona

Prueba el endpoint de login con el usuario admin:

```bash
curl -X POST https://sistema-gestion-de-inventatio.onrender.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "correo": "admin@sistema.com",
    "password": "Admin2024!"
  }'
```

**Resultado esperado:** Status 200 con token

**Si funciona, prueba el endpoint /user:**

```bash
curl -X GET https://sistema-gestion-de-inventatio.onrender.com/api/user \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

**Resultado esperado:** Status 200 con datos del admin

---

## IMPORTANTE

- Si ves "Migration table created successfully" = EXITO
- Si ves "Nothing to migrate" = La migracion ya se ejecuto antes
- Si ves error de conexion = Verifica las variables de entorno DATABASE_URL

---

## Siguiente Paso Despues de Ejecutar

Una vez ejecutada la migracion, el sistema funcionara correctamente.

**Fecha:** 2025-11-18  
**Prioridad:** CRITICA

