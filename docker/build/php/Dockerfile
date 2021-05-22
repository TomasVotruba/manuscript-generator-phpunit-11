FROM php:8-cli-alpine
RUN apk add icu-dev
RUN docker-php-ext-configure intl && docker-php-ext-install intl
COPY custom-php.ini $PHP_INI_DIR/conf.d/
