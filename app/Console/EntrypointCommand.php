<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonException;
use Railgun\Support\PHPPMStatus;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function array_map;
use function config;
use function env;
use function explode;
use function is_json;
use function json_encode;
use function pcntl_async_signals;
use function pcntl_signal;
use function preg_match;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const SIGINT;
use const SIGQUIT;
use const SIGTERM;

/**
 * Entrypoint command.
 *
 * @codeCoverageIgnore
 */
class EntrypointCommand extends Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'entrypoint {mode=server} {action=start} {-o|--output=}';
    /** @var string[] The signals to handle. */
    private array $signals = [
        SIGINT  => 'INT',
        SIGQUIT => 'QUIT',
        SIGTERM => 'TERM',
    ];
    /** @var Process The active process. */
    private Process $process;

    /**
     * @return int The command POSIX exit code.
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function handle(): int
    {
        $command = $this->argument('mode');

        switch ($command) {
            case 'server':
                return $this->server();
            case 'worker':
                return $this->worker();
                break;
            default:
                throw new InvalidArgumentException("Unsupported command {$command}, exiting");
        }
    }

    /**
     * Handle server-related commands.
     *
     * @return int The command POSIX exit code.
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    private function server(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'start':
                return $this->work('server', [
                    'vendor/bin/ppm',
                    'start',
                    '--port=' . env('PHPPM_PORT', 8080),
                    '--workers=' . env('PHPPM_WORKERS', 8),
                    '--logging=0',
                    '--max-execution-time=10',
                    '--bootstrap=laravel',
                ]);
            case 'status':
                /** @var PHPPMStatus $status */
                $status = Container::getInstance()->make(PHPPMStatus::class);

                if ($this->option('output') === 'json') {
                    $json = json_encode($status->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
                    $this->getOutput()->writeln($json);
                }

                return $status->status !== 'healthy' ? 1 : 0;
            default:
                throw new InvalidArgumentException("Unsupported action {$action}, exiting");
        }
    }

    /**
     * Handle worker-related commands.
     *
     * @return int The command POSIX exit code.
     */
    private function worker(): int
    {
        $action = $this->argument('action');

        if ($action !== 'start') {
            throw new InvalidArgumentException("Unsupported action {$action}, exiting");
        }

        return $this->work('worker', ['./artisan', 'queue:work']);
    }

    /**
     * Execute a command in a {@see Process}.
     *
     * @param string   $command The command name.
     * @param string[] $args    The command arguments.
     *
     * @return int The command POSIX exit code.
     */
    private function work(string $command, array $args): int
    {
        // Set verbosity
        $this->verbosity = $this->getOutput()->getVerbosity();
        if ($this->verbosity === OutputInterface::VERBOSITY_VERBOSE) {
            $args[] = '-v';
        } elseif ($this->verbosity === OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $args[] = '-vv';
        }

        // Start the process
        $release = config('app.release');
        Log::info("Starting Railgun $release in $command mode");
        $this->process = new Process($args);
        $this->process->setTimeout(null);

        $this->shutdown();
        $this->process->start();

        foreach ($this->process as $type => $data) {
            array_map(fn($line) => $this->log($line, $type), explode("\n", $data));
        }

        return 0;
    }

    /**
     * Parse a line of logs emitted by the {@see Process}.
     *
     * @param string $line The log line content.
     * @param string $type The log type ({@see Process::OUT} and {@see Process::ERR}).
     */
    private function log(string $line, string $type = Process::OUT): void
    {
        if ($line === '') {
            // We do not want to log empty lines
            return;
        }

        $ignorePatterns = [
            '/^\|\s+[a-zA-Z-_]+\s+\|/',
            '/^\+[-+]+\+$/',
            '/^--- Worker \d+ stderr ---$/',
        ];

        foreach ($ignorePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return;
            }
        }

        $level = $type === Process::OUT ? 'info' : 'error';

        if (is_json($line)) {
            $this->output->writeln($line);
        } else {
            Log::log($level, $line);
        }
    }

    /**
     * Gracefully handle the application shutdown.
     */
    private function shutdown(): void
    {
        pcntl_async_signals(true);

        foreach ($this->signals as $signal => $name) {
            pcntl_signal($signal, function ($signal) use ($name): void {
                Log::notice("Received SIG{$name}, shutdown Railgun. Bye bye!");
                $this->process->signal($signal);
            });
        }
    }
}
