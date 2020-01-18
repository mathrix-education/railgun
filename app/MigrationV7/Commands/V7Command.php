<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Str;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use RuntimeException;
use Symfony\Component\Process\Process;
use function collect;
use function database_path;
use function glob;
use function is_dir;
use function mkdir;
use function rename;
use function str_repeat;
use function str_replace;
use function strlen;

abstract class V7Command extends BaseCommand
{
    protected function newLine($count = 1): void
    {
        $this->getOutput()->newLine($count);
    }

    protected function title(string $string): void
    {
        $header = "| <info>$string</info> |";
        $border = '+' . str_repeat('-', strlen($string) + 2) . '+';

        $this->line($border);
        $this->line($header);
        $this->line($border);
    }

    protected function migrateTo(string $name): void
    {
        $migrationsPath    = database_path('migrations');
        $migrationsTmpPath = database_path('migrations-tmp');

        if (is_dir($migrationsTmpPath)) {
            Process::fromShellCommandline("rm -rf $migrationsTmpPath")->run();
        }

        if (!mkdir($migrationsTmpPath, 0755) && !is_dir($migrationsTmpPath)) {
            throw new RuntimeException("Directory \"$migrationsTmpPath\" was not created");
        }

        $found = false;

        collect(glob($migrationsPath . '/*.php'))
            ->filter(static function (string $path) use ($name, &$found) {
                if ($found) {
                    return true;
                }

                if (Str::contains($path, $name)) {
                    $found = true;
                }

                return false;
            })
            ->map(static function (string $source) use ($migrationsPath, $migrationsTmpPath) {
                $destination = str_replace($migrationsPath, $migrationsTmpPath, $source);

                return [$source, $destination];
            })
            ->each(static function (array $paths) {
                [$source, $destination] = $paths;
                rename($source, $destination);
            })
            ->tap(fn() => $this->call('migrate'))
            ->each(static function (array $paths) {
                [$source, $destination] = $paths;
                rename($destination, $source);
            })
            ->tap(static function () use ($migrationsTmpPath) {
                if (!is_dir($migrationsTmpPath)) {
                    return;
                }

                Process::fromShellCommandline("rm -rf $migrationsTmpPath")->run();
            });
    }
}
