<?php

namespace App\Console\Commands;

use App\Models\User;
use Braintree\Subscription;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Class TestCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TestCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "test";


    public function handle()
    {
        $result = gateway()->transaction()->sale([
            "amount" => 0,
            "customerId" => "cus_265bcc2af7e033ed9477340ab5ab2224"
        ]);
        dd($result);
    }
}
