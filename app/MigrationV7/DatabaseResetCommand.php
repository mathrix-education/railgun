<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Symfony\Component\Process\Process;
use function env;
use function storage_path;
use function str_repeat;
use function str_replace;
use function strlen;

class DatabaseResetCommand extends V7Command
{
    private const BACKUP_NAME = 'artifacts/mathrix_education_v7.sql';

    protected $signature = 'v7:reset';

    public function handle(): void
    {
        $this->title('Restoring backup');
        $this->callSilent('db:wipe', ['--force' => true]);

        $dbFile = storage_path(self::BACKUP_NAME);
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT', 3306);
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        $cmd = "pv $dbFile | mysql $dbName --host=$dbHost --port=$dbPort -u'$dbUser' -p'$dbPass'";
        $this->line(str_replace($dbPass, str_repeat('*', strlen($dbPass)), $cmd));
;
        $restoreProcess = Process::fromShellCommandline($cmd);
        $restoreProcess->setTimeout(300);
        $restoreProcess->setTty(Process::isTtySupported());
        $restoreProcess->run();
    }
}
