<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SandboxCommand extends Command
{
    protected $signature = 'sandbox';

    public function handle(): void
    {
        $result = Storage::put('test.txt', 'lol');
        dump($result);
    }
}
