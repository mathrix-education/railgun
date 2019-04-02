FROM alpine:3.9
EXPOSE 8080
STOPSIGNAL SIGTERM

WORKDIR /var/www

RUN apk -U add nginx \
  && ln -sf /dev/stdout /var/log/nginx/access.log \
  && ln -sf /dev/stderr /var/log/nginx/error.log

COPY ./docs/deploy/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docs/deploy/nginx/vhost.conf /etc/nginx/conf.d/default.conf
COPY ./docs/out/index.html /var/www/index.html

CMD ["nginx", "-g", "daemon off;"]
