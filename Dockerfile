# Alpine build, nginx, PHP 7.2 and the code itself
FROM alpine:3.9

ARG COMMIT_SHORT_SHA=unknown

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>
USER root
WORKDIR /var/www/

EXPOSE 80
EXPOSE 9000

RUN apk -U add \
    nginx \
    curl \
    php7 \
    php7-fileinfo \
    php7-fpm \
    php7-curl \
    php7-dom \
    php7-gd \
    php7-intl \
    php7-json \
    php7-mbstring \
    php7-pdo_mysql \
    php7-phar \
    php7-redis \
    php7-xdebug \
    php7-xml \
    php7-zip

COPY deploy/nginx/nginx.conf /etc/nginx/nginx.conf
COPY deploy/nginx/vhost.conf /etc/nginx/conf.d/default.conf
COPY deploy/php7/php.ini /etc/php7/php.ini
COPY deploy/php7/php-fpm.conf /etc/php7/php-fpm.conf
COPY deploy/php7/www.conf /etc/php7/php-fpm.d/www.conf

ADD . .

RUN echo "${COMMIT_SHORT_SHA}" >> version.txt

# Clean
RUN rm -rf \
    /var/cache/apk/*

HEALTHCHECK CMD curl -f http://localhost

STOPSIGNAL SIGTERM

CMD nginx && php-fpm7 --allow-to-run-as-root && tail -f /dev/null
