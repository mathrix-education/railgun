#!/bin/bash
rm -rf storage/framework/cache/dump-before.sql
mysqldump \
    -u$(printenv DB_USERNAME) \
    -p$(printenv DB_PASSWORD) \
    --host=$(printenv DB_HOST) \
    --port=$(printenv DB_PORT) \
    --complete-insert \
    --no-create-info \
    --set-gtid-purged=OFF \
    $(printenv DB_DATABASE) > storage/framework/cache/dump-before.sql
php artisan migrate:fresh
pv storage/framework/cache/dump-before.sql | mysql --database=$(printenv DB_DATABASE) \
    -u$(printenv DB_USERNAME) \
    -p$(printenv DB_PASSWORD) \
    --host=$(printenv DB_HOST) \
    --port=$(printenv DB_PORT) \
