<?php

namespace App\Console\Commands\Pyramid;

use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidFixCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidFixCommand extends BaseCommand
{
    protected $signature = "pyramid:fix";

    public function handle() {
        $this->block("Fixing zero-dates in users tables...");
        DB::unprepared(file_get_contents(__DIR__ . "/sql/fix-users-date-zeros.sql"));
        $this->success("Zero-dates fixed!");
    }
}
