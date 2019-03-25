<?php

namespace App\Console\Commands\Pyramid;

use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidSEOCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidSEOCommand extends BaseCommand
{
    protected $signature = "pyramid:seo";


    public function handle()
    {
        // Migrate paths
        $this->block("Migrate paths seo_index");
        $moduleIds = DB::table("paths")
            ->select("module_id")
            ->where("seo_index", "=", 1)
            ->get()
            ->pluck("module_id");

        DB::table("modules")->whereIn("id", $moduleIds)->update(["seo_index" => 1]);
        $this->success("Paths migrated.");

        // Activities SEO
        $ignored = 0;
        $this->block("Migrate seo_title, seo_description, seo_keywords from activities");
        $modulesSEO = DB::table("modules")
            ->select("id", "activity_id")
            ->where("type", "=", "course")
            ->get();

        $this->output->progressStart($modulesSEO->count());
        foreach ($modulesSEO as $moduleSEO) {
            $results = DB::table("activities")
                ->select(["seo_title", "seo_description", "seo_keywords"])
                ->where("id", "=", $moduleSEO->activity_id)
                ->get();

            if ($results->count() > 0) {
                $seoFields = ((array)$results->first());

                if ($moduleSEO->id === 116) {
                    dd($seoFields);
                }
                DB::table("modules")
                    ->where("id", "=", $moduleSEO->id)
                    ->update($seoFields);
            } else {
                $ignored++;
            }
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $successLine = "SEO fields migrated.";
        if ($ignored > 0) {
            $successLine .= " $ignored modules ignored";
        }
        $this->success($successLine);
    }
}
