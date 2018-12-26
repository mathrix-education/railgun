FROM php:7.2-fpm-alpine

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>

USER root

RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories

RUN apk add -U mysql-client \
    autoconf \
    gcc \
    dpkg-dev \
    dpkg \
    libmagic \
    file \
    musl-dev \
    libc-dev \
    g++ \
    make \
    re2c \
    libssh2-dev \
    libpng-dev \
    icu-dev

RUN pecl update-channels

RUN docker-php-ext-install gd \
    intl \
    mysqli \
    pdo_mysql \
    zip

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN pecl install ssh2-1.1.2 \
    && docker-php-ext-enable ssh2

RUN pecl install xdebug-2.6.1 \
    && docker-php-ext-enable xdebug

RUN curl --silent --show-error https://getcomposer.org/installer --output /tmp/installer.php \
    && php /tmp/installer.php --install-dir=/usr/bin --filename=composer \
    && rm /tmp/installer.php
