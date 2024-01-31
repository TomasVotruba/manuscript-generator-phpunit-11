FROM php:8.2-cli

RUN IPE_ICU_EN_ONLY=1 apt-get -y update \
  && apt-get install -y libicu-dev \
  && docker-php-ext-configure intl  \
  && docker-php-ext-install intl \
  && rm -rf /var/lib/apt/lists/*

RUN apt-get -y update \
    && apt-get install -y imagemagick \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get -y update \
    && curl http://ftp.de.debian.org/debian/pool/main/x/xcftools/xcftools_1.0.7-6+deb10u1_amd64.deb -o /tmp/xcftools.deb \
    && ls /tmp/xcftools.deb \
    && apt-get install -y -f /tmp/xcftools.deb \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get -y update \
    && apt-get install -y libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

COPY custom-php.ini $PHP_INI_DIR/conf.d/
COPY --from=composer /usr/bin/composer /usr/bin/composer
