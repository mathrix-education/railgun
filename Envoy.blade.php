@servers(['web00' => 'mathrix_dev@web00.mathrix.fr', 'web01' => 'mathrix@web01.mathrix.fr'])

@setup
    $repository = "git@gitlab.com:mathrix/mathrix-api.git";
    $release = date("Y-m-d_H-i-s");
@endsetup

@task('deploy_master', ['on' => 'web01'])
    branch=master
    releases_dir=/var/www/${branch}/releases

    echo Cloning {{ $repository }}
    [ -d ${releases_dir} ] || ${releases_dir}
    git clone --depth 1 {{ $repository }} ${releases_dir}/{{ $new_release_dir }} --branch ${branch}

    sh scripts/setup_release.sh ${branch} {{ $release }}
@endtask

@task('deploy_dev', ['on' => 'web00'])
    branch=dev
    releases_dir=/var/www/${branch}/releases

    echo Cloning {{ $repository }}
    [ -d ${releases_dir} ] || ${releases_dir}
    git clone --depth 1 {{ $repository }} ${releases_dir}/{{ $new_release_dir }} --branch ${branch}

    sh scripts/setup_release.sh ${branch} {{ $release }}
@endtask
