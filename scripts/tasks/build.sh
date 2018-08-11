#!/bin/sh
echo "Building build.tar.gz"
tar -zcvf build.tar.gz \
    apidoc/ \
    app/ \
    bootstrap/ \
    config/ \
    database/ \
    docs/openapi/ \
    public/ \
    resources/ \
    scripts/tasks/setup_release.sh \
    storage/oauth-private.key \
    storage/oauth-public.key \
    apidoc.json \
    artisan \
    composer.json \
    composer.lock \
    gulpfile.js \
    package.json \
    yarn.lock
echo "Done building build.tar.gz"
