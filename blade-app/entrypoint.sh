#!/bin/bash

set -e 

composer install --no-interaction --prefer-dist

php artisan key:generate --force 
php artisan config:clear
php artisan view:clear

chown -R www-data:www-data storage bootstrap/cache

exec "$@"