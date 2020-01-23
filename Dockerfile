FROM mathrix/lumen-base:0.2.0-beta.1

LABEL Maintainer="Mathieu Bour <mathieu@mathrix.fr>" \
      Description="Mathrix Education API image"

COPY --chown=nobody . /var/www/
