#!/bin/bash
# Authenticate using OAuth to GitHub
pattern="(\"@mathrix\/[a-z\-]+\": \")git\+ssh:\/\/git(@github\.com\/mathrix-education\/[a-z\-]+\.git\")"
token=$(cat storage/keychain/github-oauth.github.com)
replace="git\+https:\/\/${token}:x-oauth-basic"
sed -i -E "s/${pattern}/\1${replace}\2/g" package.json

yarn install
yarn run mails:compile
