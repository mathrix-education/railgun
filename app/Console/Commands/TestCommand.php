<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Test command which is mainly used as a sandbox during development.
 * The content of the TestCommand::handle() may be kept empty.
 *
 * @codeCoverageIgnore
 */
class TestCommand extends Command
{
    /** @var string The command signature. */
    protected $signature = 'test';

    public function handle(): void
    {
    }
}
