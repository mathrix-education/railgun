#!/bin/bash
branch=$1

# Setup SSH key
'which ssh-agent || ( apt-get update -y && apt-get install openssh-client -y )'
eval $(ssh-agent -s)
chmod 0600 storage/ssh/${branch}_id_rsa
+
ssh-add storage/ssh/${branch}_id_rsa
mkdir -p ~/.ssh
echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config

# Run envoy task
~/.composer/vendor/bin/envoy run deploy_${branch}
