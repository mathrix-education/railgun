<?php

namespace App\Console\Commands\Braintree;

use Carbon\Carbon;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

class BraintreeSyncCommand extends BaseCommand
{
    protected $signature = "braintree:sync";


    public function handle()
    {
        $yes = $this->confirm("You are about to begin a complete Braintree synchronisation. This is going to to"
            . " a (very very very) long time and may erase some data. Are you sure that you want to proceed?");

        if (!$yes) {
            $this->line("Aborting.");
        } else {
            $start = Carbon::now();
            $this->call("braintree:sync:customers");
            $this->call("braintree:sync:subscriptions");
//            $this->call("braintree:sync:transactions");
            $end = Carbon::now();

            $delta = $end->timestamp - $start->timestamp;
            $hours = (int)($delta / 3600);
            $delta -= $hours * 3600;
            $minutes = (int)($delta / 60);
            $delta -= $minutes * 60;
            $seconds = $delta;

            $this->line("Done in $hours hours $minutes minutes and $seconds seconds.");
        }
    }
}
