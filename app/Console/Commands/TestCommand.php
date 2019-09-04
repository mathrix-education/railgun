<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
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
        dd(json_encode(Carbon::now()));
    }
}
