FROM composer:latest as composer
WORKDIR /build
ADD . .
RUN composer install --prefer-dist --ignore-platform-reqs --no-interaction --no-suggest


FROM alpine:3.8
MAINTAINER Mathieu Bour <mathieu@mathrix.fr>
USER root
WORKDIR /var/www
RUN apk -U add \
    openrc \
    nginx \
    libcrypto1.0 \
    libzip \
    curl \
    php7 \
    php7-fpm \
    php7-curl \
    php7-gd \
    php7-intl \
    php7-json \
    php7-mbstring \
    php7-pdo_mysql \
    php7-phar \
    php7-redis \
    php7-ssh2 \
    php7-xdebug \
    php7-xml \
    php7-zip
COPY deploy/vhost.conf /etc/nginx/conf.d/default.conf
COPY --from=composer /build /var/www

# Clean
RUN rm -rf \
    /var/cache/apk/*

CMD tail -f /dev/null
