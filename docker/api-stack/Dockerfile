FROM composer:latest as composer
WORKDIR /build
ADD . .
RUN composer global require hirak/prestissimo
RUN composer install --prefer-dist --ignore-platform-reqs --no-interaction --no-suggest

# Alpine build, PHP 7.2 and the code itself
FROM alpine:3.8

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>
USER root
WORKDIR /var/www

EXPOSE 9000

RUN apk -U add \
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

COPY deploy/php-fpm.conf /etc/php7/php-fpm.conf
COPY deploy/www.conf /etc/php7/php-fpm.d/www.conf

COPY --from=composer /build /var/www

# Clean
RUN rm -rf \
    /var/cache/apk/*

STOPSIGNAL SIGTERM

CMD php-fpm7 --allow-to-run-as-root && tail -f /dev/null
