FROM alpine:3.8

ARG COMMIT_SHORT_SHA=unknown

MAINTAINER Mathieu Bour <mathieu@mathrix.fr>
USER root
WORKDIR /var/www

RUN apk -U add \
    nginx

# Copy configs
COPY docker/api-nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/api-nginx/vhost.conf /etc/nginx/conf.d/default.conf

RUN sed -i "s/COMMIT_SHORT_SHA/${COMMIT_SHORT_SHA}/g" /etc/nginx/conf.d/default.conf

# Clean
RUN rm -rf \
    /var/cache/apk/*

STOPSIGNAL SIGTERM

CMD nginx \
    && tail -f /dev/null
