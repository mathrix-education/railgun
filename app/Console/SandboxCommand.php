<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Stripe\Customer;
use Stripe\Exception\InvalidRequestException;
use function dd;

class SandboxCommand extends Command
{
    protected $signature = 'sandbox';

    public function handle()
    {
        try {
            Customer::retrieve('lol');
        } catch (InvalidRequestException $e) {
            dd($e->getStripeCode());
        }
    }
}
