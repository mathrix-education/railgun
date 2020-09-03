<?php

declare(strict_types=1);

namespace Railgun\Console\GCloud;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JsonException;
use Symfony\Component\Process\Process;
use function json_decode;
use function trim;
use const JSON_THROW_ON_ERROR;

class GoogleCloudCommand extends Command
{
    /**
     * Write a string as error output and exit.
     *
     * @param string          $string    The error message.
     * @param int|string|null $verbosity The message verbosity.
     */
    public function fatal(string $string, $verbosity = null): void
    {
        $this->error($string, $verbosity);
        exit(1);
    }

    /**
     * @param string $cmd  The gcloud command arguments.
     * @param null   $json If the output should be parsed as a json.
     *
     * @return int The command exit code.
     */
    public function gcloud(string $cmd, &$json = null): int
    {
        $cmd = trim($cmd);

        if (!Str::startsWith($cmd, 'gcloud')) {
            $cmd = "gcloud $cmd";
        }

        if ($json !== null && !Str::contains($cmd, "--format=json")) {
            $cmd .= " --format=json";
        }

        $this->line($cmd, null, 'v');
        $process = Process::fromShellCommandline($cmd);
        $process->run();

        if ($json !== null) {
            try {
                $json = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                $this->fatal('Invalid JSON output');
            }
        }

        return $process->getExitCode() ?? 0;
    }
}
