FROM php:8.5-fpm
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD ["php-fpm"]
