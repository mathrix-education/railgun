#!/bin/sh
echo "Building emails"
node_modules/.bin/gulp mjml

echo "Building build.tar.gz"
tar -zcf build.tar.gz \
    app/ \
    bootstrap/ \
    config/ \
    database/ \
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
