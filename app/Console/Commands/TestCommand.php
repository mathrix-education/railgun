<?php

namespace App\Console\Commands;

use App\Models\Product;
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
        dd(Product::query()->findOrFail(288)->lineage()->toArray());
    }
}
