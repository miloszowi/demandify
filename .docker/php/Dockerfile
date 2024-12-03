FROM php:8.3-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    librabbitmq-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    && pecl install amqp \
    && docker-php-ext-enable pdo_pgsql zip amqp


COPY --from=composer:2.8.3 /usr/bin/composer /usr/bin/composer

RUN mkdir -p var/cache var/log && chown -R www-data:www-data var/cache var/log

EXPOSE 9000

CMD ["php-fpm"]