FROM alpine:3.8
MAINTAINER Mathieu Bour <mathieu@mathrix.fr>
USER root
WORKDIR /var/www

RUN apk -U add \
    nginx

# Copy configs
COPY deploy/nginx.conf /etc/nginx/nginx.conf
COPY deploy/vhost.conf /etc/nginx/conf.d/default.conf

# Clean
RUN rm -rf \
    /var/cache/apk/*

STOPSIGNAL SIGTERM

CMD nginx \
    && tail -f /dev/null
