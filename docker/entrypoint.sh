#!/bin/sh
cmd=${1:-server}

case $cmd in
server)
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
    echo "Available commands are server|migrate|worker"
    exit 1
    ;;
esac
