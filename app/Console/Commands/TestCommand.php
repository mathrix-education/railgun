<?php

namespace App\Console\Commands;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use Stripe\Subscription;

/**
 * Class TestCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TestCommand extends BaseCommand
{
    protected $signature = "test";


    public function handle()
    {
        $sub = Subscription::retrieve([
            "id" => "sub_Fm6Nh0X2WkhFb9",
            "expand" => ["default_source"]
        ]);

        $this->line(json_encode($sub, JSON_PRETTY_PRINT));
    }
}
