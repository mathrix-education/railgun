<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidFixCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class Pyramid01FixCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "pyramid:fix";

    public function handle() {
        $this->cell("Fixing zero-dates in users tables...");
        DB::unprepared(file_get_contents(__DIR__ . "/sql/fix-users-date-zeros.sql"));
        $this->cell("Zero-dates fixed!", false);
    }
}
