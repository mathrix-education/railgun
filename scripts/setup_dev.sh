#!/bin/bash
# Setup project for development
composer install
yarn install
gulp install
php artisan ide-helper:generate
cp config/env/.env.local .env
mysql -uroot -p < create_database.sql
php artisan migrate
php artisan ide-helper:generate
