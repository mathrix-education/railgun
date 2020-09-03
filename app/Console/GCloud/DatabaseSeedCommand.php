<?php

declare(strict_types=1);

namespace Railgun\Console\GCloud;

use Symfony\Component\Process\Process;
use function env;

/**
 * Seed the local database using of the online database on Google Cloud.
 *
 * @codeCoverageIgnore
 */
class DatabaseSeedCommand extends GoogleCloudCommand
{
    use InteractsWithCloudSQL;

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'gcloud:db:seed {--source=education_production}';

    public function handle(): int
    {
        $instance = $this->selectInstance();
        $database = $this->selectDatabase(
            $instance['name'],
            'Select the source database',
            $this->option('source'),
        );

        $object = $this->exportDatabase($instance, $database);
        $local  = [
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ];

        $this->line("Seeding from <info>{$database['name']}</info>...");
        $script = <<<SH
        mysql -h {$local['host']} -u {$local['username']} -p'{$local['password']}' << SQL
        DROP DATABASE IF EXISTS {$local['database']};
        CREATE DATABASE {$local['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
        SQL

        gsutil cp "$object" - \
            | gzip -d \
            | sed -e "s/{$database['name']}/{$local['database']}/g" \
            | pv \
            | mysql -h {$local['host']} -u {$local['username']} -p'{$local['password']}'
        SH;

        $this->line($script, null, 'v');

        $proc = Process::fromShellCommandline($script)->setTty(Process::isTtySupported())->setTimeout(null);
        $proc->run();

        $this->call('migrate');

        return self::SUCCESS;
    }
}
