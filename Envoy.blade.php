@servers(['web00' => 'mathrix_dev@web00.mathrix.fr', 'web01' => 'mathrix@web01.mathrix.fr'])

@setup
    $repository = "git@gitlab.com:mathrix/mathrix-api.git";
    $release = date("Y-m-d_H-i-s");
@endsetup

@task('deploy_master', ['on' => 'web01'])
    branch=master
    releases_dir=/var/www/${branch}/api/releases
    new_release_dir=${releases_dir}/{{ $release }}

    echo Cloning {{ $repository }}
    [ -d ${releases_dir} ] || mkdir ${releases_dir}
    git clone --depth 1 {{ $repository }} ${new_release_dir} --branch ${branch}

    sh ${new_release_dir}/scripts/setup_release.sh ${branch} {{ $release }}
@endtask

@task('deploy_dev', ['on' => 'web00'])
    branch=dev
    releases_dir=/var/www/${branch}/api/releases
    new_release_dir=${releases_dir}/{{ $release }}

    echo Cloning {{ $repository }}
    [ -d ${releases_dir} ] || mkdir ${releases_dir}
    git clone --depth 1 {{ $repository }} ${new_release_dir} --branch ${branch}

    sh ${new_release_dir}/scripts/setup_release.sh ${branch} {{ $release }}
@endtask
