<?php

namespace App\Console\Commands\GCloud;

use Carbon\Carbon;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class CloneProductionDatabaseCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.2-rc1
 */
class CloneProductionDatabaseCommand extends BaseCommand
{
    protected $signature = "gcloud:db:clone";

    const DUMP_PATH = "framework/cache/dump_%date%.sql";
    const DB_HOST = "35.240.13.165";
    const DB_PORT = "3306";
    const DB_USERNAME = "datastudio";
    const DB_PASSWORD = "salome3004";


    public function handle()
    {
        $dumpPath = storage_path(
            str_replace("%date%", Carbon::now()->format("YmdHis"), self::DUMP_PATH)
        );

        if (file_exists($dumpPath)) {
            unlink($dumpPath);
        }

        $command = [
            'mysqldump',
            '-u' . self::DB_USERNAME,
            '-p' . self::DB_PASSWORD,
            '--host=' . self::DB_HOST,
            '--port=' . self::DB_PORT,
            '--set-gtid-purged=OFF',
            'master_mathrix_education',
            '| pv |',
            'mysql',
            '--database=' . env("DB_DATABASE"),
            '-u' . env("DB_USERNAME"),
            '-p' . env("DB_PASSWORD"),
            '--host=' . env("DB_HOST"),
            '--port=' . env("DB_PORT"),
        ];

        $this->line(implode(" ", $command));
    }
}
