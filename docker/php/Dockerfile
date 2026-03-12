FROM php:8.4-fpm

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    git unzip \
    libzip-dev zlib1g-dev \
    libicu-dev \
    pkg-config \
 && (apt-get install -y --no-install-recommends libfbclient2-dev || apt-get install -y --no-install-recommends firebird-dev) \
 && docker-php-ext-install intl zip pdo_firebird \
 && apt-get purge -y --auto-remove $PHPIZE_DEPS \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
