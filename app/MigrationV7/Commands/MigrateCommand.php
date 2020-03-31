<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Facades\DB;
use Throwable;
use function array_search;
use function array_splice;
use function round;
use function time;

/**
 * Run the full migration to Railgun V7.
 */
class MigrateCommand extends V7Command
{
    private const BANNER   = <<<'ASCII'
    ███╗   ███╗ █████╗ ████████╗██╗  ██╗██████╗ ██╗██╗  ██╗    ██╗   ██╗███████╗  ██████╗     ██████╗
    ████╗ ████║██╔══██╗╚══██╔══╝██║  ██║██╔══██╗██║╚██╗██╔╝    ██║   ██║╚════██║ ██╔═████╗   ██╔═████╗
    ██╔████╔██║███████║   ██║   ███████║██████╔╝██║ ╚███╔╝     ██║   ██║    ██╔╝ ██║██╔██║   ██║██╔██║
    ██║╚██╔╝██║██╔══██║   ██║   ██╔══██║██╔══██╗██║ ██╔██╗     ╚██╗ ██╔╝   ██╔╝  ████╔╝██║   ████╔╝██║
    ██║ ╚═╝ ██║██║  ██║   ██║   ██║  ██║██║  ██║██║██╔╝ ██╗     ╚████╔╝    ██║██╗╚██████╔╝██╗╚██████╔╝
    ╚═╝     ╚═╝╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝╚═╝  ╚═╝      ╚═══╝     ╚═╝╚═╝ ╚═════╝ ╚═╝ ╚═════╝
    ASCII;
    private const COMMANDS = [
        'v7:reset',
        'v7:zeros',
        'v7:initialization',
        'v7:flashcards',
        'v7:subjects',
        'v7:exercises',
        'v7:courses',
        'v7:comments',
        'v7:posts',
        'v7:exams',
        'v7:products',
        'v7:acquisitions',
        'v7:coupons',
        'v7:interactions',
        'v7:votes',
        'v7:reports',
        'v7:clean',
        'v7:subscriptions',
        'v7:users',
        'v7:sessions',
        'v7:counters',
        'migrate',
    ];

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'v7 {--from=} {--to=}';

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $start = time();

        // Banner
        $this->output->writeln(self::BANNER);
        $this->line('Migration script by Mathieu Bour and Valentin Pollart');
        $this->line("\n");

        foreach ($this->getCommands() as $command) {
            $now = $this->timeMs();

            try {
                DB::beginTransaction();
                $this->call($command);
                DB::commit();
            } catch (Throwable $exception) {
                DB::rollBack();
                $this->alert("Error while executing command $command");
                throw $exception;
            }

            $duration = $this->timeMs() - $now;

            if ($duration > 10000) {
                $durationSeconds = round($duration / 1000, 1);
                $this->info("> Done in {$durationSeconds} s");
            } else {
                $this->info("> Done in {$duration} ms");
            }
            $this->line('');
        }

        $duration = time() - $start;
        $this->line("> Finished in $duration seconds!");
    }

    /**
     * Using the --from and --to options, get the list of commands to execute.
     *
     * @return string[] The commands to execute.
     */
    private function getCommands(): array
    {
        $commands = self::COMMANDS;

        $from = array_search($this->option('from'), $commands, true);
        if ($from !== false) {
            array_splice($commands, 0, $from);
        }
        $to = array_search($this->option('to'), $commands, true);
        if ($to !== false) {
            array_splice($commands, $to + 1);
        }

        return $commands;
    }
}
