#!/bin/bash

case "$1" in
    server)
        /usr/sbin/nginx && /usr/sbin/php-fpm7 --allow-to-run-as-root
        ;;
    worker)
        /usr/bin/php /var/www/artisan queue:work --sleep=3 --tries=3
        ;;
    scheduler)
        while true; do
            sleep 60
            /usr/bin/php /var/www/artisan schedule:run
        done
        ;;
    *)
        echo "Usage: server|worker|scheduler"
        exit 1
esac
