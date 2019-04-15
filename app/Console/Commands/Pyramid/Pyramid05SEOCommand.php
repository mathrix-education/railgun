<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidSEOCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class Pyramid05SEOCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "pyramid:seo";


    public function handle()
    {
        // Migrate paths
        $this->line("Migrate paths seo_index");
        $moduleIds = DB::table("paths")
            ->join("activity_module", "paths.activity_id", "=", "activity_module.activity_id")
            ->selectRaw("paths.activity_id, MIN(activity_module.module_id) as module_min_id")
            ->where("seo_index", "=", 1)
            ->groupBy("paths.activity_id")
            ->get()
            ->pluck("module_min_id");

        DB::table("modules")
            ->whereIn("id", $moduleIds)
            ->where("type", "=", "course")
            ->update(["seo_index" => 1]);

        $this->line("Paths migrated.");


        $this->makeSeoTitles();
    }


    public function makeSeoTitles()
    {
        $activityIds = DB::table("modules")
            ->select("activity_id")
            ->whereNotNull("activity_id")
            ->orderBy("activity_id")
            ->groupBy("activity_id")
            ->get()
            ->pluck("activity_id");
        $arrow = "← Mathrix";
        $typeMappings = [
            "mcq" => "Exercice",
            "test" => "Interrogation",
            "flashcard" => "Flashcard"
        ];

        $this->line("Generating seo_title and seo_slug");

        $this->progressStart($activityIds->count());

        foreach ($activityIds as $activityId) {
            $activity = DB::table("activities")
                ->select(["name", "seo_title"])
                ->where("id", "=", $activityId)
                ->get()
                ->first();

            $seoTitle = trim(str_replace($arrow, "", $activity->seo_title));
            if (empty($seoTitle)) {
                $seoTitle = trim(str_replace($arrow, "", $activity->name));
            }

            $modules = DB::table("modules")
                ->select(["id", "type"])
                ->where("activity_id", "=", $activityId)
                ->orderBy("order")
                ->get()
                ->values();

            if ($modules->isEmpty()) {
                continue;
            } else if ($modules->count() === 1 && $modules->first()->type === "course") {
                $module = $modules->first();

                DB::table("modules")
                    ->where("id", "=", $module->id)
                    ->update([
                        "order" => 0,
                        "seo_title" => $seoTitle,
                        "seo_slug" => Str::slug($seoTitle)
                    ]);
            } else {
                $indices = [
                    "mcq" => 1,
                    "test" => 1,
                    "flashcard" => 1
                ];

                foreach ($modules as $order => $module) {
                    $mapped = isset($typeMappings[$module->type]) ? $typeMappings[$module->type] : null;
                    $index = isset($indices[$module->type]) ? $indices[$module->type] : null;

                    if ($mapped !== null && $index !== null) {
                        //$moduleSeoTitle = "$seoTitle - $mapped $index";
                        $moduleSeoTitle = $seoTitle;
                        $indices[$module->type]++;
                    } else {
                        $moduleSeoTitle = $seoTitle;
                    }

                    DB::table("modules")
                        ->where("id", "=", $module->id)
                        ->update([
                            "order" => $order,
                            "seo_title" => $moduleSeoTitle,
                            "seo_slug" => Str::slug($moduleSeoTitle)
                        ]);
                }
            }

            $this->progressAdvance();
        }
        $this->progressFinish();
    }
}
