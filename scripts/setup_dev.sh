#!/bin/bash

# Setup project for development
composer install
yarn install
gulp install
php artisan ide-helper:generate
cp config/env/.env.dev .env
