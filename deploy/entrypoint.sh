#!/bin/bash

case "$1" in
    serve)
        nginx && /usr/bin/php-fpm7 --allow-to-run-as-root
        ;;
    queue)
        /usr/bin/php /var/www/artisan queue:work --sleep=3 --tries=3
        ;;
    schedule)
        while true; do
            sleep 60
            /usr/bin/php /var/www/artisan schedule:run
        done
        ;;
    *)
        echo "Usage: serve|queue|schedule"
        exit 1
esac
