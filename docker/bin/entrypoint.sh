#!/usr/bin/env ash

shutdown() {
    echo '{"severity":"NOTICE","message":"Shutdown Railgun, bye-bye!"}'
    kill -TERM $process
}

# Extract mode from args
mode=$1
shift

case $mode in
server)
    artisan lighthouse:clear-cache
    artisan migrate
    cmd="ppm start ${*} | gcloud-logs &"
    ;;
worker)
    cmd="artisan queue:work ${*} | gcloud-logs &"
    ;;
ready)
    result=$(ppm status | grep healthy)

    if [ -z "$result" ]
    then
        exit 1
    else
        exit 0
    fi
    ;;
*)
    echo "Invalid \"$1\" argument"
    exit 1
    ;;
esac

trap shutdown QUIT
trap shutdown INT
trap shutdown TERM

eval "$cmd"
process="$!"
wait "$process"
