@servers(['web' => 'mathrix_dev@82.165.68.94'])

@setup
    $repository = 'git@gitlab.com:mathrix/mathrix-api.git';
@endsetup

@task('deploy')
    $app_dir = '/var/www/dev/api';
    $releases_dir = $app_dir . '/releases';
    $shared_dir = $app_dir . '/shared';

    $release = date('Y-m-d_H-i-s');
    $new_release_dir = $releases_dir .'/'. $release;

    echo 'Cloning repository'
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone --depth 1 {{ $repository }} {{ $new_release_dir }} --branch dev

    echo "Starting deployment ({{ $release }})"
    cd {{ $new_release_dir }}
    composer install --prefer-dist --no-scripts -q -o

    echo "Linking storage directory"
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $shared_dir }}/storage {{ $new_release_dir }}/storage

    echo 'Linking .env file'
    ln -nfs {{ $app_dir }}/.env {{ $new_release_dir }}/.env

    echo 'Linking current release'
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
@endtask
