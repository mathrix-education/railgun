FROM mathrix/lumen-base:0.1.3

LABEL Maintainer="Mathieu Bour <mathieu@mathrix.fr>" \
      Description="Mathrix Education API image"

COPY --chown=nobody . /var/www/
