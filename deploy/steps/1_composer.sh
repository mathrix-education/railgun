#!/bin/bash
# Authenticate using OAuth to GitHub
token=$(cat storage/keychain/github-oauth.github.com)
composer config -g github-oauth.github.com ${token}

composer install \
  --optimize-autoloader \
  --ignore-platform-reqs \
  --no-interaction \
  --no-suggest \
  --no-progress \
  --no-ansi
