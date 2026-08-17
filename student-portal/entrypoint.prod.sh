#!/bin/sh

if [ "$1" = '/usr/bin/supervisord' ] || [ -z "$1" ]; then

    echo "Esperando a MariaDB..."

    until php -r "
        try {
            new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'));
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    "; do
        echo "MariaDB aun no esta lista..."
        sleep 2
    done

    echo "MariaDB lista"

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan migrate --force

fi

exec "$@"