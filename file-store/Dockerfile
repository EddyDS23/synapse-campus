FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]