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
    cp {{ $new_release_dir }}/storage/env/.env.dev {{ $new_release_dir }}/.env
    cd {{ $new_release_dir }}
    composer install --prefer-dist --no-scripts -q -o

    echo 'Linking storage directory'
    [ -d {{ $shared_dir }} ] || mkdir {{ $shared_dir }}
    [ -d {{ $shared_dir }}/storage ] || cp -r {{ $new_release_dir }}/storage {{ $shared_dir }}/storage
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $shared_dir }}/storage {{ $new_release_dir }}/storage

    echo 'Linking current release'
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