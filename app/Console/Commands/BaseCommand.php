<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class BaseCommand
 *
 * @package App\Console\Commands
 */
abstract class BaseCommand extends Command
{
    public function block(string $string, $style = null, $verbosity = null)
    {
        $string = " $string ";
        $l = str_repeat(" ", strlen($string));

        $this->line($l, $style, $verbosity);
        $this->line($string, $style, $verbosity);
        $this->line($l, $style, $verbosity);
    }

    public function success(string $string, $verbosity = null)
    {
        $this->line("[<info>✔</info>] $string", null, $verbosity);
    }
}