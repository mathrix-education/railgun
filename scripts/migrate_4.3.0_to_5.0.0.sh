#!/bin/ash
php artisan migrate --step=1 # Migrate before destruction of the linking tables
php artisan pyramid
php artisan migrate
