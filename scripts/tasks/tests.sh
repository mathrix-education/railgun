#!/bin/bash
branch=$1

cp config/env/.env.pipelines .env
composer install --prefer-dist -q --no-ansi --no-interaction --no-suggest
php artisan migrate:refresh --seed

if [[ "$branch" = "master" ]]
then
    sudo ./vendor/bin/paratest -p16 --runner=WrapperRunner --coverage-text --configuration=phpunit.xml --stop-on-failure
else
    sudo ./vendor/bin/paratest -p16 --runner=WrapperRunner --configuration=phpunit.xml
fi
