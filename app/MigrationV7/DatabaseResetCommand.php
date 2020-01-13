<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use Symfony\Component\Process\Process;
use function env;
use function storage_path;

class DatabaseResetCommand extends BaseCommand
{
    private const BACKUP_NAME = 'artifacts/mathrix_education_v7-ori.sql';

    protected $signature = 'v7:reset';

    public function handle(): void
    {
        $this->info('Cleaning database');
        $this->callSilent('db:wipe', ['--force' => true]);
        $this->info('Cleaning database: OK');

        $this->info('Restoring backup');
        $dbFile = storage_path(self::BACKUP_NAME);
        $dbHost = env('DB_HOST');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        $cmd = "pv $dbFile | mysql $dbName --host=$dbHost -u'$dbUser' -p'$dbPass'";

        $restoreProcess = Process::fromShellCommandline($cmd);
        $restoreProcess->setTty(Process::isTtySupported());
        $restoreProcess->run();
        $this->info('Restoring backup: OK');
    }
}
