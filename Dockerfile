FROM edbizarro/gitlab-ci-pipeline-php:7.2-alpine

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>

USER root

RUN apk add -U autoconf libssh2 \
    && pecl install ssh2-1.1.2 \
    && docker-php-ext-enable ssh2

RUN php -m