#!/bin/sh

# Building variables
environment=$1
server=$2
release=`date '+%Y-%m-%d_%H-%M-%S'`
releases_dir=/var/www/${environment}/api/releases
new_release_dir=${releases_dir}/${release}

if [ "$server" = "web00" ]
then
    user=mathrix_dev
    host=web00.mathrix.fr
elif [ "$server" = "web01" ]
then
    user=mathrix_dev
    host=web01.mathrix.fr
fi

# Setup SSH configuration
apt-get update -y
apt-get install openssh-client -y
eval $(ssh-agent -s)
chmod 0600 storage/ssh/${server}_id_rsa
ssh-add storage/ssh/${server}_id_rsa
mkdir -p ~/.ssh
echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config

# Transfer build and deploy
${user}@${host} "mkdir -p $new_release_dir"
scp build.tar.gz ${user}@${host}:${new_release_dir}/build.tar.gz
sh ${new_release_dir}/scripts/setup_release.sh ${environment} ${release}
