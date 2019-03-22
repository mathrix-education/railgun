#!/bin/bash
php artisan migrate:refresh
php artisan db:seed --class=ContentTableSeeder
php artisan pyramid:migrate
