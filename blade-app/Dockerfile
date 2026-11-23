FROM php:8.3-fpm

RUN apt-get update && apt install -y \
    libzip-dev \
    libonig-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# Install Node.js 20 LTS
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT [ "/entrypoint.sh" ]
CMD [ "php-fpm" ]



