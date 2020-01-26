FROM php:7.4-fpm-alpine

LABEL Maintainer="Mathieu Bour <mathieu@mathrix.fr>" \
      Description="Mathrix Education API image"

WORKDIR /var/www

# Install additional packages:
# - gmp for the php-gmp extension required to use ellipitic curves
# - gettext to use the envsubst command
RUN apk add --no-cache gmp gmp-dev gettext fcgi \
    && docker-php-ext-install -j$(nproc) gmp opcache pdo_mysql \
    && rm -rf /usr/src /var/www /var/cache/apk/* /usr/local/bin/docker-php-entrypoint

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/healthcheck.sh /usr/local/bin/healthcheck
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Make sure files/folders needed by the processes are accessible when they run under the nobody user
RUN chown -R nobody.nobody /run && \
    chown -R nobody.nobody /var/www

# Declare healthcheck
HEALTHCHECK CMD /usr/local/bin/healthcheck || exit 1

USER nobody

ENTRYPOINT ["/usr/local/bin/entrypoint"]
