<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Carbon;
use Symfony\Component\Process\Process;
use function env;
use function implode;

class RestoreCommand extends V7Command
{
    private const SQL_INSTANCE    = 'mathrix-education:europe-west1:mysql';
    private const SERVICE_ACCOUNT = 'p731492094632-9gcerm@gcp-sa-cloud-sql.iam.gserviceaccount.com';
    private const BUCKET          = 'mathrix-backups';

    protected $signature = 'v7:restore {target}';

    public function handle(): void
    {
        $target = $this->argument('target');

        $database = env('DB_DATABASE');
        $date     = Carbon::now()->format('Y-m-d_H-i-s');
        $export   = "$target-$date.sql.gz";
        $gsObject = 'gs://' . self::BUCKET . "/$target/$export";

        $this->title('Exporting database to Google Cloud Storage');
        $cmd = implode(' ', [
            "mysqldump {$this->getMySQLOptions()} --databases $database",
            '  --hex-blob --skip-triggers --order-by-primary --no-autocommit',
            '  --default-character-set=utf8mb4 --single-transaction',
            "| sed 's/$database/$target/g'",
            '| gzip | pv',
            "| gsutil cp - $gsObject",
        ]);
        $this->line($cmd);

        Process::fromShellCommandline($cmd)
            ->setTimeout(300)
            ->setTty(Process::isTtySupported())
            ->run();

        $this->title('Importing database from Google Cloud Storage');
        $cmd = implode(' ', [
            'gsutil acl ch -u ' . self::SERVICE_ACCOUNT . ':W gs://' . self::BUCKET . ' &&',
            'gsutil acl ch -u ' . self::SERVICE_ACCOUNT . ":R $gsObject &&",
            'gsutil sql import sql ' . self::SQL_INSTANCE . " $gsObject --database=$target &&",
            'gsutil acl ch -d ' . self::SERVICE_ACCOUNT . ' gs://' . self::BUCKET . ' &&',
            'gsutil acl ch -d ' . self::SERVICE_ACCOUNT . " $gsObject",
        ]);
        $this->line($cmd);

        Process::fromShellCommandline($cmd)
            ->setTimeout(600)
            ->setTty(Process::isTtySupported())
            ->run();
    }
}
