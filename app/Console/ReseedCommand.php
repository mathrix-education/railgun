<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use function env;
use function in_array;

class ReseedCommand extends Command
{
    protected const SAFE_HOSTS = ['localhost', '127.0.0.1'];

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'reseed';

    public function handle(): int
    {
        $unsafe = !in_array(env('DB_HOST'), self::SAFE_HOSTS, true);

        if ($unsafe) {
            $sure = $this->confirm('Unsafe database host (' . env('DB_HOST') . '), seed anyway?');

            if (!$sure) {
                $this->line('Wise choice.');

                return self::FAILURE;
            }

            $sure = $this->confirm('Are you REALLY REALLY sure?');

            if (!$sure) {
                $this->line('Wise choice.');

                return self::FAILURE;
            }
        }

        $this->call('migrate:fresh', ['--seed' => 1, '--force' => 1]);
        $this->call('stripe:sync:products', ['--intent' => 'update']);

        return self::SUCCESS;
    }
}
