#!/bin/sh
cmd=${1:-start}

case $cmd in
start)
    php /var/www/artisan lighthouse:clear-cache
    php-fpm
    ;;
migrate)
    php /var/www/artisan migrate
    php /var/www/artisan lighthouse:clear-cache
    ;;
worker)
    php /var/www/artisan queue:work
    ;;
*)
    echo "Invalid command: \"$cmd\"."
    exit 1
    ;;
esac
