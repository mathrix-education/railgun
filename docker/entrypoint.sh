#!/bin/sh
cmd=${1:-run}

case $cmd in
run)
    php-fpm
    ;;
migrate)
    php /var/www/bin/artisan migrate
    php /var/www/bin/artisan lighthouse:clear-cache
    ;;
*)
    echo "Invalid command: \"$cmd\"."
    exit 1
    ;;
esac
