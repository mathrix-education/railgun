<?php

namespace App\Console\Commands\Redoc;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class RedocBundle.
 * Bundle Redoc to a single HTML file.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 */
class RedocBundle extends Command
{
    private const REDOC_COMMAND = "redoc-cli bundle docs/openapi/index.yaml";
    private const REDOC_OUTPUT_FILE = "redoc-static.html";
    private const REDOC_DESTINATION_FILE = "docs/openapi/index.html";
    protected $signature = "redoc:bundle";

    public function handle()
    {
        $this->line("Bundling Redoc...");

        $process = new Process(self::REDOC_COMMAND);
        $process->start();

        while ($process->isRunning()) {
            echo $process->getOutput();
            $process->clearOutput();
        }

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $appPath = app()->basePath();
        $outputFile = $appPath . "/" . self::REDOC_OUTPUT_FILE;
        $destinationFile = $appPath . "/" . self::REDOC_DESTINATION_FILE;

        $this->line("Moving " . self::REDOC_OUTPUT_FILE . " to " . self::REDOC_DESTINATION_FILE);
        rename($outputFile, $destinationFile);
    }
}
