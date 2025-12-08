FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    git \
    unzip \
    $PHPIZE_DEPS \
    linux-headers

RUN pecl install pcov && docker-php-ext-enable pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN echo "pcov.enabled=1" >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini

CMD ["php", "-v"]
