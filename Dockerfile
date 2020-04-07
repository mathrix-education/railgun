FROM php:7.4-fpm-alpine

LABEL Maintainer="Mathieu Bour <mathieu@mathrix.fr>" \
      Description="Mathrix Education API image"

ARG RELEASE="no-release"
ARG EXT_REDIS_VERSION="5.2.1"

ENV APP_RELEASE=$RELEASE

WORKDIR /var/www

# Install additional packages:
# - gmp for the php-gmp extension required to use ellipitic curves
# - gettext to use the envsubst command
RUN mkdir -p /usr/src/php/ext && \
    # Download ext-redis source code
    wget -q -O /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/${EXT_REDIS_VERSION}.tar.gz && \
    tar xzf /tmp/redis.tar.gz -C /tmp && \
    mv /tmp/phpredis-${EXT_REDIS_VERSION} /usr/src/php/ext/redis && \
    rm -rf /tmp/*redis* && \
    # Install PHP extensions
    apk add --no-cache gmp gmp-dev fcgi pcre-dev libzip libzip-dev ${PHPIZE_DEPS} && \
    docker-php-ext-install -j$(nproc) pdo_mysql zip opcache gmp redis && \
    apk del gmp-dev pcre-dev libzip-dev ${PHPIZE_DEPS} && \
    rm -rf /usr/src \
        /tmp/redis.tar.gz \
        /usr/local/bin/docker-php-entrypoint \
        /usr/local/etc/php-fpm.conf.default \
        /usr/local/etc/php-fpm.conf.d/www.conf.default \
        /var/www/* \
        /var/cache/apk/* && \
    php -m

# Copy configuriations and scripts
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/healthcheck.sh /usr/local/bin/healthcheck
COPY docker/etc/. /usr/local/etc/
COPY docker/scripts/ /usr/local/scripts/
RUN php /usr/local/scripts/access.format.php \
    && rm -rf /usr/local/scripts /usr/local/etc/**/*.stub*

# Copy source code
COPY --chown=www-data . /var/www/

# Install dependencies with composer, set release
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo && \
    composer install --no-suggest --prefer-dist --no-dev --optimize-autoloader && \
    composer clear-cache && \
    rm -rf /usr/bin/composer /root/.composer && \
    sed -i "s/RELEASE_ALIAS/$RELEASE/g" /var/www/config/app.php /var/www/config/sentry.php

# Make sure files/folders needed by the processes are accessible when they run under the www-data user
#RUN chown -R www-data.www-data /run && \
#    chown -R www-data.www-data /var/www

# Declare healthcheck
# HEALTHCHECK CMD /usr/local/bin/healthcheck || exit 1 # TODO: change it when we go stable
HEALTHCHECK CMD exit 0

USER www-data

ENTRYPOINT ["/usr/local/bin/entrypoint"]
