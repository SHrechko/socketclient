FROM php:8.1-fpm

RUN apt update \
    && apt install -y git zip \
    && docker-php-ext-install -j$(nproc) mysqli pdo_mysql \
    && docker-php-ext-install sockets

WORKDIR /var/www/socketserver

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD ["php-fpm"]