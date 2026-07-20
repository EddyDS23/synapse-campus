#!/bin/bash

if [ "$1" = 'php-fpm' ] || [ -z "$1" ]; then

composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan key:generate --force

echo "Esperando a MariaDB"

until php -r "
    try{
        new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'));
        exit(0);
    }catch(Exception \$e){
        exit(1);
    }
";do
    echo "MariaDB aun no lista"
    sleep 2;
done

echo "MariaDB lista"

php artisan migrate --force
php artisan vendor:publish --provider="Dedoc/Scramble/ScrambleServiceProvider"
php artisan config:clear
php artisan cache:clear
exec php-fpm

fi

exec "$@"
