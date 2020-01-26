#!/bin/sh
php /var/www/bin/artisan gcloud:setup "$APP_ENV"
php-fpm
