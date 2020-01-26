FROM php:7.4-fpm-alpine

LABEL Maintainer="Mathieu Bour <mathieu@mathrix.fr>" \
      Description="Mathrix Education API image"

WORKDIR /var/www

# Install additional packages:
# - gmp for the php-gmp extension required to use ellipitic curves
# - gettext to use the envsubst command
RUN apk add --no-cache gmp gmp-dev gettext fcgi pcre-dev ${PHPIZE_DEPS} \
    && pecl install -o redis-5.1.1 \
    && docker-php-ext-enable redis \
    # && apk del pcre-dev ${PHPIZE_DEPS} \
    && docker-php-ext-install -j$(nproc) gmp opcache pdo_mysql \
    && rm -rf /usr/src \
        /var/www /var/cache/apk/* \
        /usr/local/bin/docker-php-entrypoint \
        /usr/local/etc/php-fpm.conf.default \
        /usr/local/etc/php-fpm.conf.d/www.conf.default \
    && php -m

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/healthcheck.sh /usr/local/bin/healthcheck
COPY docker/etc/. /usr/local/etc/

COPY --chown=www-data . /var/www/

# Make sure files/folders needed by the processes are accessible when they run under the www-data user
RUN chown -R www-data.www-data /run && \
    chown -R www-data.www-data /var/www

# Declare healthcheck
HEALTHCHECK CMD /usr/local/bin/healthcheck || exit 1

USER www-data

ENTRYPOINT ["/usr/local/bin/entrypoint"]
