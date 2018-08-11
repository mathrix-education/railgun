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

echo "Building storage directory"
[ -d ${shared_dir} ] || mkdir ${shared_dir}
[ -d ${shared_dir}/storage ] || mkdir ${shared_dir}/storage
cp -R ${new_release_dir}/storage/* ${shared_dir}/storage
rm -rf ${new_release_dir}/storage
[ -d ${shared_dir}/storage/backups ] || mkdir ${shared_dir}/storage/backups
[ -d ${shared_dir}/storage/backups/cdn ] || mkdir ${shared_dir}/storage/backups/cdn
[ -d ${shared_dir}/storage/backups/database ] || mkdir ${shared_dir}/storage/backups/database
[ -d ${shared_dir}/storage/framework ] || mkdir ${shared_dir}/storage/framework
[ -d ${shared_dir}/storage/framework/cache ] || mkdir ${shared_dir}/storage/framework/cache
[ -d ${shared_dir}/storage/framework/views ] || mkdir ${shared_dir}/storage/framework/views
[ -d ${shared_dir}/storage/logs ] || mkdir ${shared_dir}/storage/logs
[ -d ${shared_dir}/storage/sitemaps ] || mkdir ${shared_dir}/storage/sitemaps

echo "Setup CDN"
[ -d ${shared_dir}/storage/cdn ] || mkdir ${shared_dir}/storage/cdn

echo "Linking storage"
ln -nfs ${shared_dir}/storage/ ${new_release_dir}/storage

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
rm ${new_release_dir}/build.tar.gz

echo "$release successfully installed"
