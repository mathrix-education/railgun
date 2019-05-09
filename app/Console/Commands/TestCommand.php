<?php

namespace App\Console\Commands;

use App\Services\Braintree\Utils\Formatter;
use Braintree\CreditCard;
use Braintree\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mathrix\Lumen\Bases\BaseCommand;

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
        $sub = Subscription::find("sub_dd781c6c3578366796631806deda06fd");

        dd(Formatter::cast($sub->jsonSerialize()));
    }
}
