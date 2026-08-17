# =============================================
# Etapa 1: Dependencias de Composer (Producción)
# =============================================
FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
# Instala dependencias sin scripts ni autoload (aún no está el código completo)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader

# Copia todo el código para poder generar el autoloader
COPY . .

# Regenera el autoloader con el code nuevo
RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --no-scripts

# =============================================
# Etapa 2: Runtime (PHP-FPM + Nginx)
# =============================================
FROM php:8.2-fpm-alpine

# Dependencias del sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        intl \
        zip \
        gd \
        opcache \
        bcmath \
        soap \
        xml \
        fileinfo \
    && rm -rf /var/cache/apk/*

# Copia las librerías de producción desde la etapa vendor
COPY --from=vendor /app /var/www/html
WORKDIR /var/www/html

# Permisos Laravel
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 775 /var/www/html

# Copia configuración de Nginx y Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Script de entrada para prepbootstrap
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Variables por defecto (se sobreescriben desde Dockploy)
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_LOCALE=es \
    APP_FALLBACK_LOCALE=es \
    APP_FAKER_LOCALE=es_PE \
    DB_CONNECTION=mysql \
    DB_PORT=3306 \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync \
    SESSION_DRIVER=file

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]