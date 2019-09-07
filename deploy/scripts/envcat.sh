#!/usr/bin/env bash
# Print a Laravel Lumen environment file and convert it to gcloud --set-env-vars format
cat $1 | sed -e '/^$/d' | sed -z 's/\n/\,/g' | sed -e 's/,*$//g'
