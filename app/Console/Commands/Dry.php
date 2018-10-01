<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class Dry.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class Dry extends Command
{
    protected $signature = "dry";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $a = 15;
        $b = 0;

        try {
            $c = $a / $b; // "Risqué"
        } catch (\Exception $e) {
            // Si ça crashe
            dd($e);
        } finally {
            // Dans tous les cas
        }

        $this->line("Hello world");
    }
}
