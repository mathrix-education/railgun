<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidMarkCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since
 */
class Pyramid09MarksStatsCommand extends BaseCommand
{
    use BoxStyle;
    protected $signature = "pyramid:marks:stats";


    public function handle()
    {
        // Generate statistics
        $total = DB::table("marks")->count();
        $broken = DB::table("marks")->where("markable_id", "=", 0)->count();
        $percentage = round(($broken / $total) * 100, 2);

        $this->cell("Broken marks: $percentage%");
    }
}
