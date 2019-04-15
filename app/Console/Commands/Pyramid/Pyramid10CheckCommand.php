<?php

namespace App\Console\Commands\Pyramid;

use App\Models\Chapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class PyramidChecker.
 * Check that the migrate module paths. This verification is exhaustive, which mean that all paths are checked.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class Pyramid10CheckCommand extends BaseCommand
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
            if ($this->search($line)) {
                $this->output->progressAdvance();
            } else {
                VarDumper::dump($line);
                $this->fatal("Error found.");
            }
        }
        $this->output->progressFinish();

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
        $this->success("Mappings loaded");

        $counter = 0;

        // Generate dataset
        $this->line("Generating dataset (this might take a while)...");
        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            $chapterNotionMappings = DB::table("chapter_notion")
                ->where("chapter_id", "=", $chapter->id)
                ->orderBy("order")
                ->get();

            /** @var \stdClass $chapterNotionMapping [chapter_id, notion_id, order] */
            foreach ($chapterNotionMappings as $chapterNotionMapping) {
                $notionActivityMappings = DB::table("activity_notion")
                    ->where("notion_id", "=", $chapterNotionMapping->notion_id)
                    ->orderBy("order")
                    ->get();

                /** @var \stdClass $notionActivityMapping [notion_id, activity_id, order] */
                foreach ($notionActivityMappings as $notionActivityMapping) {
                    $activityModuleMappings = DB::table("activity_module")
                        ->where("activity_id", "=", $notionActivityMapping->activity_id)
                        ->orderBy("order")
                        ->get();

                    /** @var \stdClass $activityModuleMappings [activity_id, module_id, order] */
                    foreach ($activityModuleMappings as $activityModuleMapping) {
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
                            "module_questions" => $module->questions,

                            "module" => [
                                "type" => $module->type,
                                "content" => $module->content,
                                "questions" => $module->questions,
                                "choices" => $module->choices,
                                "corrections" => $module->corrections
                            ]
                        ]);
                    }
                }
            }
        }

        $this->output->write("\n");
        $this->success("Dataset generated.");


        return $dataset;
    }


    public function search($line)
    {
        $wheres = [];
        $nulls = [];

        foreach ($line["module"] as $key => $value) {
            if ($value === null) {
                $nulls[] = $key;
            } else {
                $wheres[] = ["modules.$key", "=", $value];
            }
        }

        $wheres = array_merge($wheres, [
            ["chapters.name", "=", $line["chapter_name"]],
            ["notions.name", "=", $line["notion_name"]],
            ["activities.name", "=", $line["activity_name"]]
        ]);

        $query = DB::table("modules")
            ->join("activities", "activities.id", "=", "modules.activity_id")
            ->join("notions", "notions.id", "=", "activities.notion_id")
            ->join("chapters", "chapters.id", "=", "notions.chapter_id");

        if (!empty($wheres)) {
            $query = $query->where($wheres);
        }

        if (!empty($nulls)) {
            foreach ($nulls as $null) {
                $query = $query->whereNull($null);
            }
        }

        return $query->count() > 0;
    }
}
