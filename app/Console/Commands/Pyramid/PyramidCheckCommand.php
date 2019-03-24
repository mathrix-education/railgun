<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BaseCommand;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class PyramidChecker.
 * Check that the migrate module paths. This verification is exhaustive, which mean that all paths are checked.
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
        // Generating dataset
        $this->block("Generating dataset...");
        $dataset = $this->makeDataset();

        // Check existence of every route
        $this->block("Checking integrity...");

        $this->output->progressStart($dataset->count());
        foreach ($dataset as $line) {
            $this->search($line);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        // Print broke models
        $this->block("Looking for broken models...");

        $brokenNotions = DB::table("notions")->whereNull("chapter_id")->count();
        $brokenActivities = DB::table("activities")->whereNull("notion_id")->count();
        $brokenModules = DB::table("modules")->whereNull("activity_id")->count();
        $this->line("$brokenNotions notions are not linked");
        $this->line("$brokenActivities activities are not linked");
        $this->line("$brokenModules modules are not linked");

        $this->success("Everything is correct. Well played Mr. Bour!");
    }


    /**
     * Create the verification dataset.
     * @return Collection
     */
    private function makeDataset(): Collection
    {
        $dataset = new Collection();

        // Load content models
        $this->line("Loading data...");
        $chapters = Chapter::all();
        $this->success("Data loaded");

        // Load linking tables
        $this->line("Loading mappings...");
        $mappingsCN = DB::table("chapter_notion")->get();
        $mappingsNA = DB::table("activity_notion")->get();
        $mappingsAM = DB::table("activity_module")->get();
        $this->success("Mappings loaded");

        $counter = 0;

        // Generate dataset
        $this->line("Generating dataset (this might take a while)...");
        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            $chapterNotionMappings = $mappingsCN
                ->where("chapter_id", "=", $chapter->id)
                ->sortBy("order");

            /** @var \stdClass $chapterNotionMapping [chapter_id, notion_id, order] */
            foreach ($chapterNotionMappings as $chapterNotionMapping) {
                $notionActivityMappings = $mappingsNA
                    ->where("notion_id", "=", $chapterNotionMapping->notion_id)
                    ->sortBy("order");

                /** @var \stdClass $notionActivityMapping [notion_id, activity_id, order] */
                foreach ($notionActivityMappings as $notionActivityMapping) {
                    $activityModuleMappings = $mappingsAM
                        ->where("activity_id", "=", $notionActivityMapping->activity_id)
                        ->sortBy("order");

                    /** @var \stdClass $activityModuleMappings [activity_id, module_id, order] */
                    foreach ($activityModuleMappings as $activityModuleMapping) {
                        /*$chapter = $chapters->where("id", "=", $chapterNotionMapping->chapter_id)->first();
                        $notion = $notions->where("id", "=", $notionActivityMapping->notion_id)->first();
                        $activity = $activities->where("id", "=", $activityModuleMapping->activity_id)->first();
                        $module = $modules->where("id", "=", $activityModuleMapping->module_id)->first();*/
                        $chapter = DB::table("chapters")
                            ->where("id", "=", $chapterNotionMapping->chapter_id)
                            ->get()
                            ->first();
                        $notion = DB::table("notions")
                            ->where("id", "=", $notionActivityMapping->notion_id)
                            ->get()
                            ->first();
                        $activity = DB::table("activities")
                            ->where("id", "=", $activityModuleMapping->activity_id)
                            ->get()
                            ->first();
                        $module = DB::table("modules")
                            ->where("id", "=", $activityModuleMapping->module_id)
                            ->get()
                            ->first();

                        $counter++;
                        if ($counter % 10 === 0) {
                            $this->output->write(".");
                        }
                        if ($counter % 500 === 0) {
                            $this->output->write(" $counter\n");
                        }

                        $dataset->push([
                            // Debug (not used for the verification)
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

        $this->output->write("\n");
        $this->success("Dataset generated.");


        return $dataset;
    }


    /**
     * Search a line in the database starting from module. Since we start from modules, the route
     * module => activity => notion => chapter is unique and guarantees the unicity.
     *
     * @param array $line The line to search.
     *
     * @return bool If the line was found.
     */
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
