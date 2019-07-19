#!/bin/bash
# Get port from environment variable (Cloud Run implementation)
PORT_FROM_ENV=$(printenv PORT)
PORT=${PORT_FROM_ENV:-"80"}
sed -i -E "s/CLOUD_RUN_PORT/${PORT}/g" /etc/nginx/conf.d/default.conf

/usr/sbin/php-fpm7 --allow-to-run-as-root && /usr/sbin/nginx -g 'daemon off;'
