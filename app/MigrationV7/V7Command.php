<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use function str_repeat;
use function strlen;

abstract class V7Command extends BaseCommand
{
    protected function title(string $string): void
    {
        $header = "| <info>$string</info> |";
        $border = '+' . str_repeat('-', strlen($string) + 2) . '+';

        $this->line($border);
        $this->line($header);
        $this->line($border);
    }
}
