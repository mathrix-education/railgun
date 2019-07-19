<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Braintree\Models\LineItem;
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
        LineItem::all()->each(function(LineItem $lineItem) {
           $lineItem->touch();
           $lineItem->save();
        });
    }
}
