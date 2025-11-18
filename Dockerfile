FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-client \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de composer primero para cachear dependencias
COPY inventarioBackend/composer.json inventarioBackend/composer.lock ./

# Instalar dependencias incluyendo dev (necesario para l5-swagger)
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Copiar el resto de los archivos del proyecto
COPY inventarioBackend/ /var/www/html/

# Regenerar autoload sin ejecutar scripts (los scripts se ejecutarán en runtime)
RUN composer dump-autoload --optimize --no-scripts

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Script de inicio
COPY start-docker.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start-docker.sh

# Exponer puerto (Render asigna el puerto dinámicamente)
EXPOSE 8000

CMD ["/usr/local/bin/start-docker.sh"]

