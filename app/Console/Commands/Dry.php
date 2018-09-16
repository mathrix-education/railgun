<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
        $test = [
            102, 132 => [
                "visible" => false
            ], 124, 142 => [
                "visible" => true
            ]
        ];

        foreach ($test as $key => $value) {
            $this->line($key . ":" . json_encode($value));
        }
    }
}
