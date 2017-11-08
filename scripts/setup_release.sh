#!/bin/bash
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
composer install --prefer-dist --no-scripts -q -o

echo "Running npm install"
npm install --silent

echo "Generating docs"
apidoc -i app/ -o docs/ --silent

echo "Generating emails"
gulp mjml --silent

echo "Linking storage directory"
[ -d ${shared_dir} ] || mkdir ${shared_dir} && chmod 0775 ${shared_dir}
[ -d ${shared_dir}/storage ] || cp -r ${new_release_dir}/storage ${shared_dir}/storage
[ -d ${shared_dir}/storage/cdn  ] || mkdir ${shared_dir}/cdn
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