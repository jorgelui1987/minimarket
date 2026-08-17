#!/bin/sh
set -e

echo "=== Iniciando Laravel (Dockploy) ==="

# Crea el storage de Laravel si no existe
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Permisos
chmod -R 775 storage bootstrap/cache

# Genera APP_KEY si no está definida
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generando APP_KEY..."
    APP_KEY=$(php artisan key:generate --show --force)
    export APP_KEY
fi

# Esperar a que la base de datos esté disponible (hasta 60s)
if [ "${DB_CONNECTION:-mysql}" = "mysql" ] || [ "${DB_CONNECTION:-mysql}" = "mariadb" ]; then
    echo "Esperando base de datos ${DB_HOST:-mysql}..."
    i=0
    until php -r "
        try {
            new PDO('mysql:host=' . (getenv('DB_HOST') ?: 'mysql') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME') ?: 'root', getenv('DB_PASSWORD') ?: '');
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " > /dev/null 2>&1; do
        i=$((i+1))
        if [ $i -ge 60 ]; then
            echo "ERROR: No se pudo conectar a MySQL después de 60s"
            exit 1
        fi
        echo "  ... esperando base de datos ($i/60)"
        sleep 1
    done
    echo "MySQL disponible."
fi

# Ejecutar migraciones (si se pide)
if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    echo "Ejecutando migraciones..."
    php artisan migrate --force --no-interaction || echo "AVISO: migraciones fallaron (puede estar vacío el usuario sin privilegios para CREATE)"
fi

# Optimización en producción
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "Optimizando Laravel para producción..."
    php artisan config:cache --no-interaction || true
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
fi

echo "=== Arrancando supervisord (nginx + php-fpm) ==="
exec "$@"