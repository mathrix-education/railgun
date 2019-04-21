#!/bin/bash
rm -rf storage/framework/cache/dump-before.sql
mysqldump -u'datastudio' \
    -p'salome3004' \
    --host=127.0.0.1 \
    --port=3307 \
    --complete-insert \
    --no-create-info \
    --set-gtid-purged=OFF \
    dev_mathrix_education > storage/framework/cache/dump-before.sql
php artisan migrate:fresh
pv storage/framework/cache/dump-before.sql | mysql --database=mathrix_fr_dev \
    -u'root' -p'salome3004' \
    --host=127.0.0.1 --port=3306
