#!/bin/sh
cmd=${1:-run}

case $cmd in
run)
    php /var/www/artisan lighthouse:clear-cache
    php-fpm
    ;;
migrate)
    php /var/www/artisan migrate
    php /var/www/artisan lighthouse:clear-cache
    ;;
*)
    echo "Invalid command: \"$cmd\"."
    exit 1
    ;;
esac
