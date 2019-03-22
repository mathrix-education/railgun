<?php

namespace App\Console\Commands\Migrations;

use App\Console\Commands\BaseCommand;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class PyramidChecker.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidCheckCommand extends BaseCommand
{
    protected $signature = "pyramid:check";


    public function handle()
    {
        $dataset = Cache::remember("pyramid.dataset", 15, function () {
            $this->line("Generating dataset...");

            return $this->makeDataset();
        });
        $this->line("{$dataset->count()} lines to check");

        // Check existence of every route
        $this->output->progressStart($dataset->count());
        foreach ($dataset as $line) {
            $this->search($line);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        // Print broke models
        $brokenNotions = DB::table("notions")->whereNull("chapter_id")->count();
        $brokenActivities = DB::table("activities")->whereNull("notion_id")->count();
        $brokenModules = DB::table("modules")->whereNull("activity_id")->count();
        $this->line("$brokenNotions notions are not linked");
        $this->line("$brokenActivities activities are not linked");
        $this->line("$brokenModules modules are not linked");

        $this->success("Everything is correct. Well played Mr. Bour!");
    }


    private function makeDataset(): Collection
    {
        $dataset = new Collection();
        $chapters = Chapter::all();

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            /** @var Notion $notion */
            foreach ($chapter->notions as $notion) {
                /** @var Activity $activity */
                foreach ($notion->activities as $activity) {
                    /** @var Module $module */
                    foreach ($activity->modules as $module) {
                        $module = DB::select("SELECT * FROM modules WHERE id = {$module->id}");
                        $module = current($module);

                        $dataset->push([
                            // Debug
                            "chapter_id" => $chapter->id,
                            "notion_id" => $notion->id,
                            "activity_id" => $activity->id,
                            "module_id" => $module->id,

                            // Check
                            "chapter_name" => $chapter->name,
                            "notion_name" => $notion->name,
                            "activity_name" => $activity->name,
                            "module_content" => $module->content,
                            "module_questions" => $module->questions
                        ]);
                    }
                }
            }
        }

        return $dataset;
    }


    private function search($line)
    {
        // Module
        $activityIds = DB::table("modules")
            ->select(["id"])
            ->where("content", "=", $line["module_content"])
            ->where("questions", "=", $line["module_questions"])
            ->get()
            ->pluck("id");

        if ($activityIds->count() === 0) {
            VarDumper::dump($line);
            $this->fatal("Unable to find module with content `{$line["module_content"]}` and questions "
                . "`{$line["module_content"]}`");

            return false;
        }

        // Explore each module route
        $found = "none";

        foreach ($activityIds as $activityId) {
            // Search for activity
            $results = DB::table("activities")
                ->select(["notion_id"])
                ->where("name", "=", $line["activity_name"])
                ->where("id", "=", $activityId)
                ->get()
                ->pluck("notion_id");
            if (empty($results)) {
                continue;
            } else if ($found == "none") {
                $found = "activity";
            }
            $notionId = $results->first();

            // Search for notion
            $results = DB::table("notions")
                ->select(["chapter_id"])
                ->where("name", "=", $line["notion_name"])
                ->where("id", "=", $notionId)
                ->get()
                ->pluck("chapter_id");
            if (empty($results)) {
                continue;
            } else if ($found == "activity") {
                $found = "notion";
            }
            $chapter_id = $results->first();

            // Search for chapter
            $results = DB::table("chapters")
                ->select("id")
                ->where("name", "=", $line["chapter_name"])
                ->where("id", "=", $chapter_id)
                ->get();
            if (empty($results)) {
                continue;
            } else {
                return true; // Exit here, success!
            }
        }

        // Handle error
        switch ($found) {
            case "none":
                VarDumper::dump($line);
                $this->fatal("Unable to find activity with name `{$line["activity_name"]}`");
                break;
            case "activity":
                VarDumper::dump($line);
                $this->fatal("Unable to find notion with name `{$line["notion_name"]}`");
                break;
            case "notion":
                VarDumper::dump($line);
                $this->fatal("Unable to find chapter with name `{$line["chapter_name"]}`");
                break;
        }

        return false;
    }
}
