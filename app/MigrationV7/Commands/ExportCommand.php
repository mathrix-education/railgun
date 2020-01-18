<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Carbon;
use Symfony\Component\Process\Process;
use function env;
use function storage_path;

class ExportCommand extends V7Command
{
    protected $signature = 'v7:export {target} {mode}';

    public function handle(): void
    {
        $target = $this->argument('target');

        $database       = env('DB_DATABASE');
        $date           = Carbon::now()->format('Y-m-d_H-i-s');
        $exportFileName = "$target-$date.sql.gz";

        $cmd  = "mysqldump {$this->getMySQLOptions()} --databases $database "
            . '--hex-blob --skip-triggers --order-by-primary --no-autocommit '
            . '--default-character-set=utf8mb4 --single-transaction';
        $cmd .= " | sed 's/$database/$target/g'";
        $cmd .= ' | gzip | pv';

        $mode = $this->argument('mode');

        if ($mode === 'gsutil') {
            $cmd .= " | gsutil cp - gs://mathrix-backups/$target/$exportFileName";
        } else {
            $exportFile = storage_path("artifacts/$exportFileName");
            $cmd       .= " > $exportFile";
        }

        Process::fromShellCommandline($cmd)
            ->setTimeout(300)
            ->setTty(Process::isTtySupported())
            ->run();
    }
}
