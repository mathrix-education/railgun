FROM php:7.4-alpine

LABEL Maintainer="Mathieu Bour <mathieu@mathrix.fr>" \
      Description="Mathrix Education API image"

ARG RELEASE="no-release"
ARG EXT_REDIS_VERSION="5.2.1"

ENV APP_RELEASE=$RELEASE
ENV PATH="/var/www/vendor/bin:${PATH}"

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
    docker-php-ext-install -j$(nproc) pcntl pdo_mysql zip opcache gmp redis && \
    apk del gmp-dev pcre-dev libzip-dev ${PHPIZE_DEPS} && \
    rm -rf /usr/src \
        /tmp/redis.tar.gz \
        /usr/local/bin/docker-php-entrypoint \
        /var/www/* \
        /var/cache/apk/* && \
    php -m

# Copy configuriations and scripts
COPY docker/etc/. /usr/local/etc/

# Copy source code
COPY . /var/www/

# Install dependencies with composer, set release
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo && \
    composer install --no-suggest --prefer-dist --no-dev --optimize-autoloader && \
    composer clear-cache && \
    # Hardcode release
    sed -i "s/RELEASE_ALIAS/$RELEASE/g" /var/www/config/app.php /var/www/config/sentry.php && \
    # Move binaries
    mv /var/www/docker/bin/entrypoint.sh /usr/bin/railgun && \
    mv /var/www/docker/bin/artisan.php /var/www/vendor/bin/artisan && \
    mv /var/www/docker/bin/gcloud-logs.php /var/www/vendor/bin/gcloud-logs && \
    # Cleanup
    rm -rf /usr/bin/composer /root/.composer /var/www/docker

ENTRYPOINT ["railgun"]
CMD ["ppm", "start"]
