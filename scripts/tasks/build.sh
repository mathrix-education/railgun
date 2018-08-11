#!/bin/bash
echo "Installing node packages"
yarn install --silent

echo "Building Redoc"
node_modules/.bin/redoc-cli bundle docs/openapi/index.yaml
mv redoc-static.html docs/openapi/index.html

echo "Building emails"
node_modules/.bin/gulp mjml
rm resources/views/emails/*/*.mjml
rmdir resources/views/emails/common

echo "Building build.tar.gz"
tar -zcf build.tar.gz \
    apidoc/ \
    app/ \
    bootstrap/ \
    config/ \
    database/ \
    docs/openapi/index.html \
    public/ \
    resources/ \
    scripts/tasks/setup_release.sh \
    storage/oauth-private.key \
    storage/oauth-public.key \
    apidoc.json \
    artisan \
    composer.json \
    composer.lock
echo "Done building build.tar.gz"
