<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

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
        'buster:reset',
        'buster:zeros',
        'buster:initialization',
        'buster:flashcards',
        'buster:subjects',
        'buster:exercises',
        'buster:courses',
        'buster:comments',
        'buster:posts',
        'buster:exams',
        'buster:products',
        'buster:acquisitions',
        'buster:coupons',
        'buster:interactions',
        'buster:votes',
        'buster:reports',
        'buster:clean',
        'buster:subscriptions',
        'buster:users',
        'buster:sessions',
        'buster:counters',
        'migrate',
    ];

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster {--from=} {--to=}';

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
