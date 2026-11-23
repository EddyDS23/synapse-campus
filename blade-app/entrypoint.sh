#!/bin/bash

set -e 

composer install --no-interaction --prefer-dist

# Install npm dependencies if node_modules is missing
if [ ! -d "node_modules" ]; then
    npm install
fi

php artisan key:generate --force 
php artisan config:clear
php artisan view:clear

chown -R www-data:www-data storage bootstrap/cache

exec "$@"