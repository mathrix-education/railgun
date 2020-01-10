FROM mathrix/lumen-base:0.1.3

LABEL Maintainer="Mathieu Bour <mathieu@mathrix.fr>" \
      Description="Mathrix Drive API image"

COPY --chown=nobody . /var/www/

RUN APP_SKIP_SETUP=true php artisan providers:cache -f
