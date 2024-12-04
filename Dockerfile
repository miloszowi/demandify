FROM php:8.3-fpm

WORKDIR /var/www/html

RUN groupadd -g 1000 app && \
    useradd -u 1000 -g app -m app

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

COPY --chown=1000:1000 composer.* ./

USER app

RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --chown=1000:1000 ./ /var/www/html

RUN mkdir -p var/cache var/log && chown -R app:app var/

EXPOSE 9000

CMD ["php-fpm"]