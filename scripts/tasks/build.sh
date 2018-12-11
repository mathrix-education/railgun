#!/bin/sh
echo "Installing node packages"
yarn install

echo "Building Redoc"
node_modules/.bin/redoc-cli bundle docs/openapi/index.yaml
mv redoc-static.html docs/openapi/index.html

echo "Building emails"
node_modules/.bin/gulp mjml

echo "Building build.tar.gz"
tar -zcf build.tar.gz \
    apidoc/ \
    app/ \
    bootstrap/ \
    config/ \
    database/ \
    docs/openapi/index.html \
    public/ \
    resources/views/emails/*/*.blade.php \
    scripts/tasks/setup_release.sh \
    storage/oauth-private.key \
    storage/oauth-public.key \
    apidoc.json \
    artisan \
    composer.json \
    composer.lock
echo "Done building build.tar.gz"
