#!/usr/bin/env ash
cmd="$* | gcloud-logs &"

shutdown() {
    echo '{"severity":"NOTICE","message":"Shutdown Railgun, bye-bye!"}'
    kill -TERM $process
}

trap shutdown QUIT
trap shutdown INT
trap shutdown TERM

eval "$cmd"
process="$!"
wait "$process"
