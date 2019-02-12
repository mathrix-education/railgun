#!/bin/bash

docker_build() {
    docker build \
        -f docker/$1/Dockerfile \
        -t registry.gitlab.com/mathrix/api/api/$1:latest \
        .
}

#docker_build api-code
#docker_build api-composer
#docker_build api-nginx
#docker_build api-php7.2-fpm-alpine
docker_build api-stack
