# Alpine build, nginx, PHP 7.2 and the code itself
FROM alpine:3.9

ARG BRANCH_NAME=unknown
ARG SHORT_SHA=unknown

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>
USER root
WORKDIR /var/www/

ADD https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub

RUN apk -U add ca-certificates && \
    echo "https://dl.bintray.com/php-alpine/v3.9/php-7.3" >> /etc/apk/repositories && \
    apk -U add \
        bash \
        nginx \
        curl \
        php7 \
        #php7-fileinfo \
        php7-fpm \
        php7-curl \
        php7-dom \
        php7-gd \
        php7-gmp \
        php7-intl \
        php7-json \
        php7-mbstring \
        php7-opcache \
        php7-pdo_mysql \
        php7-phar \
        #php7-redis \
        #php7-tokenizer \
        php7-xdebug \
        php7-xml \
        #php7-xmlwriter \
        php7-zip && \
        ln -nfs /usr/bin/php7 /usr/bin/php

ADD . .

COPY ./deploy/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/nginx/vhost.conf /etc/nginx/conf.d/default.conf
COPY ./deploy/php7/php.ini /etc/php7/php.ini
COPY ./deploy/php7/php-fpm.conf /etc/php7/php-fpm.conf
COPY ./deploy/php7/www.conf /etc/php7/php-fpm.d/www.conf
COPY ./deploy/entrypoint.sh /entrypoint.sh

RUN php artisan providers:cache -f && \
    echo "${SHORT_SHA}" >> build.txt && \
    chmod +x /entrypoint.sh && \
    rm -rf deploy/

# Clean
RUN rm -rf /var/cache/apk/*

HEALTHCHECK CMD exit 0

STOPSIGNAL SIGTERM

ENTRYPOINT ["/entrypoint.sh"]
