#!/usr/bin/env bash
pv storage/framework/cache/dump_master.sql | mysql -u'root' \
    -p'salome3004' \
    --host 127.0.0.1 \
    --port 3306 \
    local_mathrix_education
