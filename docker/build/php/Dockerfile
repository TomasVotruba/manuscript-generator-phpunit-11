FROM php:8-cli-alpine
RUN apk add icu-dev
RUN docker-php-ext-configure intl && docker-php-ext-install intl
COPY custom-php.ini $PHP_INI_DIR/conf.d/

# Install, configure, but don't enable Xdebug
RUN apk --no-cache add --virtual .build-dependencies \
        autoconf \
        g++ \
        make \
    && pecl install xdebug-3.0.4 \
    && apk --no-cache del .build-dependencies
COPY xdebug-cli.ini ${PHP_INI_DIR}/conf.d/xdebug.ini
