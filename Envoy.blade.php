@servers(['dev' => 'mathrix_dev@82.165.68.94'])

@setup
    $repository = 'git@gitlab.com:mathrix/mathrix-api.git';
    $release = date('Y-m-d_H-i-s');
@endsetup


@story('deploy_dev', ['on' => 'dev'])
    @php
        $branch = 'dev';
    @endphp
    deploy
    migrate
@endstory

@story('seed_dev', ['on' => 'dev'])
    @php
        $branch = 'dev';
    @endphp
    seed
@endstory

@story('reseed_dev', ['on' => 'dev'])
    @php
        $branch = 'dev';
    @endphp
    reseed
@endstory


@task('deploy')
    echo 'Setting up vars'
    @php
        $app_dir = '/var/www/' . $branch . '/api';
        $releases_dir = $app_dir . '/releases';
        $shared_dir = $app_dir . '/shared';
        $new_release_dir = $releases_dir .'/'. $release;
    @endphp

    echo 'Cloning {{ $repository }}'
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone --depth 1 {{ $repository }} {{ $new_release_dir }} --branch {{ $branch }}

    echo 'Starting deployment of {{ $release }}'
    cp {{ $new_release_dir }}/config/.env.{{ $branch }} {{ $new_release_dir }}/.env
    cd {{ $new_release_dir }}

    echo 'Running composer install'
    composer install --prefer-dist --no-scripts -q -o

    echo 'Running npm install'
    npm install --silent

    echo 'Generating docs'
    apidoc -i app/ -o docs/ --silent

    echo 'Generating emails'
    gulp mjml --silent

    echo 'Linking storage directory'
    [ -d {{ $shared_dir }} ] || mkdir {{ $shared_dir }} && chmod 0775 {{ $shared_dir }}
    [ -d {{ $shared_dir }}/storage ] || cp -r {{ $new_release_dir }}/storage {{ $shared_dir }}/storage
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $shared_dir }}/storage {{ $new_release_dir }}/storage

    echo 'Linking current release'
    [ -f {{ $app_dir }}/current ] && rm {{ $app_dir }}/current
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current

    echo 'Done'
@endtask

@task('migrate')
    @php
        $current = '/var/www/' . $branch . '/api/current';
    @endphp
    cd {{ $current }}
    php artisan migrate
@endtask

@task('seed')
    @php
        $current = '/var/www/' . $branch . '/api/current';
    @endphp
    cd {{ $current }}
    php artisan db:seed
@endtask

@task('reseed')
    @php
        $current = '/var/www/' . $branch . '/api/current';
    @endphp
    cd {{ $current }}
    php artisan migrate:refresh --seed
@endtask