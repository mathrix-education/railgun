FROM alpine:3.8

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>
USER root
WORKDIR /var/www
ADD . .

RUN apk -U add \
    libcrypto1.0 \
    libzip \
    curl \
    php7 \
    php7-cli \
    php7-curl \
    php7-gd \
    php7-intl \
    php7-json \
    php7-mbstring \
    php7-pdo_mysql \
    php7-phar \
    php7-redis \
    php7-sqlite3 \
    php7-ssh2 \
    php7-xdebug \
    php7-xml \
    php7-zip

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN composer global require hirak/prestissimo
RUN composer install --prefer-dist --ignore-platform-reqs --no-interaction --no-suggest

RUN touch database/database.sqlite && cp config/env/.env.pipelines .env
RUN php artisan migrate --seed
RUN vendor/bin/paratest

# Clean
RUN rm -rf \
    /var/cache/apk/*
