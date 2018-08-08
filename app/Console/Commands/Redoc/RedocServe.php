<?php
namespace App\Console\Commands\Redoc;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class RedocServe
 * Start Redoc development server
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Console\Commands\Redoc
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class RedocServe extends Command
{
    protected $signature = "redoc:serve";
    private const REDOC_COMMAND = "redoc-cli serve docs/openapi/index.yaml";


    public function handle()
    {
        $this->getOutput()->write("Starting Redoc embedded web server");

        $process = new Process(self::REDOC_COMMAND);
        $process->start();

        while ($process->isRunning()) {
            echo $process->getOutput();
            $process->clearOutput();
        }

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
