#!/bin/sh
cmd=${1:-server}

shutdown() {
    echo '{"severity":"NOTICE","message":"Received SIGTERM signal, shutdown Railgun. Bye bye!"}'
    kill -TERM "$child"
}

trap shutdown TERM

case $cmd in
server)
    php /var/www/artisan lighthouse:clear-cache
    php-fpm --nodaemonize &
    child=$!
    ;;
worker)
    php /var/www/artisan queue:work &
    child=$!
    ;;
*)
    echo "Invalid command: \"$cmd\"."
    echo "Available commands are server|migrate|worker"
    exit 1
    ;;
esac

echo "{\"severity\":\"NOTICE\",\"message\":\"Application running on PID: $child\"}"
wait $child
