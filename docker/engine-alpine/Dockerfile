FROM alpine:3.8

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>

USER root

WORKDIR /var/www

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

RUN curl --silent --show-error https://getcomposer.org/installer --output /tmp/installer.php \
    && php /tmp/installer.php --install-dir=/usr/bin --filename=composer \
    && rm /tmp/installer.php

# Check that the php environment is correct
ADD ./composer.json ./composer.json
RUN composer check-platform-reqs

# Clean
RUN rm -rf \
    /var/cache/apk/* \
    ./composer.json

CMD ["php", "-a"]
