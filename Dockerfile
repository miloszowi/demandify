FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk update && apk add --no-cache --update linux-headers \
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
    && pecl install amqp pcov \
    && docker-php-ext-enable amqp pcov
#    && echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.discover_client_host=true" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.idekey=docker" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


RUN groupadd -g 1000 app && \
    useradd -u 1000 -g app -m app

COPY --from=composer:2.8.4 /usr/bin/composer /usr/bin/composer

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