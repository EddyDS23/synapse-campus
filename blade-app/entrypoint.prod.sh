#!/bin/sh

if [ "$1" = '/usr/bin/supervisord' ] || [ -z "$1" ]; then

    chown -R www-data:www-data storage bootstrap/cache
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

fi

exec "$@"