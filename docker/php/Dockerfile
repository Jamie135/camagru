FROM docker.io/library/php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev libpng-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo_pgsql gd \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/php.ini

ARG UID=1000
ARG GID=1000
RUN groupmod -g ${GID} www-data \
    && usermod -u ${UID} -g ${GID} www-data \
    && chown -R www-data:www-data /var/www

WORKDIR /var/www
