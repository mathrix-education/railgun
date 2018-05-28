#!/bin/bash
# Bash file for release setup on distant server
branch=$1
release=$2

app_dir=/var/www/${branch}/api
releases_dir=${app_dir}/releases
shared_dir=${app_dir}/shared
new_release_dir=${releases_dir}/${release}
current=${app_dir}/current

echo "Starting deployment of $release"
cp ${new_release_dir}/config/env/.env.${branch} ${new_release_dir}/.env
cd ${new_release_dir}

echo "Running composer install"
composer install --prefer-dist --no-ansi --no-interaction --no-progress --optimize-autoloader

echo "Running yarn install"
yarn install --silent

echo "Running gulp install"
gulp install --silent

echo "Linking storage directory"
[ -d ${shared_dir} ] || mkdir ${shared_dir} && chmod 0775 ${shared_dir}
[ -d ${shared_dir}/storage ] || cp -R ${new_release_dir}/storage ${shared_dir}/storage
[ -d ${shared_dir}/storage/cdn ] || mkdir ${shared_dir}/storage/cdn
[ -d ${new_release_dir}/storage/cdn ] && cp -R ${new_release_dir}/storage/cdn/* ${shared_dir}/storage/cdn
rm -rf ${new_release_dir}/storage
ln -nfs ${shared_dir}/storage ${new_release_dir}/storage

echo "Running migrations"
cd ${new_release_dir}
php artisan migrate

echo "Delete oldest releases"
ls -dt ${releases_dir}/*/ | tail -n +6 | xargs rm -rf

echo "Linking current release"
[ -f ${current} ] && rm ${current}
ln -nfs ${new_release_dir} ${current}

echo "$release successfully installed"
