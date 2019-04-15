<?php

namespace App\Console\Commands\Pyramid;

use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class Pyramid12FixNullDates.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc3
 */
class Pyramid11ChangeNotionTypes extends BaseCommand
{
    protected $signature = "pyramid:change-notion-types";


    public function handle()
    {
        $this->line("Change notions type premium => normal");
        $notionIds = DB::table("notions")
            ->select("id")
            ->where("type", "=", "premium")
            ->get()
            ->pluck("id");
        DB::table("activities")
            ->whereIn("notion_id", $notionIds)
            ->update(["premium" => 1]);
        DB::table("notions")
            ->where("type", "=", "premium")
            ->update(["type" => "normal"]);
    }
}
