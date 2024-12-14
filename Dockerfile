FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk update && apk add --no-cache \
    shadow \
    postgresql-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    rabbitmq-c-dev \
    autoconf \
    g++ \
    make \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    bash \
    && pecl install amqp \
    && docker-php-ext-enable amqp

RUN groupadd -g 1000 app && \
    useradd -u 1000 -g app -m app

COPY --from=composer:2.8.3 /usr/bin/composer /usr/bin/composer

COPY --chown=1000:1000 composer.* ./

USER app

COPY --chown=1000:1000 ./ /var/www/html
# for local development
RUN composer install --no-cache

# for production image
# RUN set -eux; \
# 	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress


RUN mkdir -p var/cache var/log && chown -R app:app var/

EXPOSE 9000

CMD ["php-fpm"]