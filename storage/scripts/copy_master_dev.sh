#!/bin/bash
rm -rf storage/framework/cache/dump-before.sql
mysqldump \
    -u$(printenv DB_USERNAME) \
    -p$(printenv DB_PASSWORD) \
    --host=$(printenv DB_HOST) \
    --port=$(printenv DB_PORT) \
    master_mathrix_education > storage/framework/cache/dump-before.sql
php artisan db:destroy
pv storage/framework/cache/dump-before.sql | mysql --database=$(printenv DB_DATABASE) \
    -u$(printenv DB_USERNAME) \
    -p$(printenv DB_PASSWORD) \
    --host=$(printenv DB_HOST) \
    --port=$(printenv DB_PORT)
php artisan migrate
