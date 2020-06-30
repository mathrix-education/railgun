#!/usr/bin/env bash
DB_SOURCE=education_production
DB_DESTINATION=education_local
BUCKET="mathrix-backups"
OBJECT_SOURCE=$(gsutil ls "gs://$BUCKET/databases/education_production" | tail -n 1)

read -p "You are going seed the database $DB_SOURCE to your local database $DB_DESTINATION, continue? [Y/n]" -n 1 -r

if [[ $REPLY != "" && ! $REPLY =~ ^[Yy]$ ]]; then
    exit 1
fi

mysql -h 127.0.0.1 -u root -p'salome3004' << SQL
DROP DATABASE IF EXISTS $DB_DESTINATION;
CREATE DATABASE $DB_DESTINATION CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
SQL

gsutil cp "$OBJECT_SOURCE" - \
    | gzip -d \
    | sed -e "s/$DB_SOURCE/$DB_DESTINATION/g" \
    | pv \
    | mysql -h 127.0.0.1 -u root -p'salome3004'
