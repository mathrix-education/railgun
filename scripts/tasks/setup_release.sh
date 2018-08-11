#!/bin/bash
# Bash file for release setup on distant server
environment=$1
release=$2

app_dir=/var/www/${environment}/api
releases_dir=${app_dir}/releases
shared_dir=${app_dir}/shared
new_release_dir=${releases_dir}/${release}
current=${app_dir}/current

echo "Starting deployment of $release"
cp ${new_release_dir}/config/env/.env.${environment} ${new_release_dir}/.env
cd ${new_release_dir}

echo "Running composer install"
composer install --quiet --prefer-dist --no-ansi --no-interaction --no-progress --optimize-autoloader --no-suggest

echo "Running yarn install"
yarn install --silent

echo "Running gulp install"
gulp install --silent

echo "Building documentation"
redoc-cli bundle ${new_release_dir}/docs/openapi/index.yaml
mv ${new_release_dir}/redoc-static.html ${new_release_dir}/docs/openapi/index.html

echo "Linking storage directory"
[ -d ${shared_dir} ] || mkdir ${shared_dir}
[ -d ${shared_dir}/storage ] || mkdir ${shared_dir}/storage && cp -R ${new_release_dir}/storage ${shared_dir}/storage
[ -d ${shared_dir}/storage/cdn ] || mkdir ${shared_dir}/storage/cdn
ln -nfs ${shared_dir}/storage ${new_release_dir}/storage

echo "Running migrations"
cd ${new_release_dir}
php artisan migrate

echo "Delete oldest releases"
ls -dt ${releases_dir}/*/ | tail -n +6 | xargs rm -rf

echo "Linking current release"
[ -f ${current} ] && rm ${current}
ln -nfs ${new_release_dir} ${current}

echo "Cleaning build"
rm -rf ${new_release_dir}/scripts
rm ${new_release_dir}/build.targ.gz

echo "$release successfully installed"
