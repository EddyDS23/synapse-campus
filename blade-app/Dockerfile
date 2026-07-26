FROM php:8.3-fpm

RUN apt-get update && apt install -y \
    libzip-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring zip
    
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT [ "/entrypoint.sh" ]
CMD [ "php-fpm" ]



