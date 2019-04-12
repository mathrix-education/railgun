<?php

namespace App\Console\Commands\Pyramid;

use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidMarkCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since
 */
class PyramidMarksCommand extends BaseCommand
{
    protected $signature = "pyramid:marks";


    public function handle()
    {
        $this->block("Migrating marks...");
        $this->call("pyramid:marks:activities");
        $this->call("pyramid:marks:modules");

        // Generate statistics
        $total = DB::table("marks")->count();
        $broken = DB::table("marks")->where("markable_id", "=", 0)->count();
        $percentage = round(($broken / $total) * 100, 2);

        $this->block("Broken marks: $percentage%");
    }
}
