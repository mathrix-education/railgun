<?php

namespace App\Console\Commands\Pyramid;

use App\Models\Activity;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidMarks.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidMarksActivitiesCommand extends BaseCommand
{
    protected $signature = "pyramid:marks:activities";
    /** @var Collection */
    private $mappings;


    public function handle()
    {
        $this->mappings = new Collection();

        $this->makeMappings();
        $this->applyMappings();
    }


    private function resolveActivity($path)
    {
        if (!empty($this->mappings[$path->id])) {
            return true;
        }

        $resolved = Activity::query()
            ->where("name", "=", $path->name)
            ->whereHas("notion.chapter", function (Builder $query) use ($path) {
                return $query->where("id", "=", $path->chapter_id);
            })
            ->whereHas("modules", function (Builder $query) use ($path) {
                return $query->where("content", "=", $path->content)
                    ->where("questions", "=", $path->questions)
                    ->where("corrections", "=", $path->corrections)
                    ->where("solutions", "=", $path->solutions);
            })
            ->first();

        if ($resolved instanceof Activity) {
            $this->mappings->offsetSet($path->id, $resolved->id);
            return true;
        } else {
            return false;
        }
    }


    private function makeMappings()
    {
        $this->block("Building paths <=> activities mappings...");
        //$data = collect(DB::select(file_get_contents(__DIR__ . "/sql/get-broken-paths-activities.sql")));
        $data = DB::table("paths")
            ->join("activities", "paths.activity_id", "=", "activities.id")
            ->join("modules", "paths.activity_id", "=", "modules.activity_id")
            ->whereNotNull("paths.activity_id")
            ->whereNull("paths.module_id")
            ->select(
                "paths.id",
                "paths.chapter_id",
                "activities.name",
                "modules.content",
                "modules.questions",
                "modules.corrections",
                "modules.solutions"
            )
            ->get();

        $total = DB::table("paths")
            ->whereNotNull("activity_id")
            ->whereNull("module_id")
            ->count();
        $failCount = 0;

        $this->output->progressStart($data->count());
        $data->each(function ($path) use (&$failCount) {
            if (!$this->resolveActivity($path)) {
                $failCount++;
            }
            $this->output->progressAdvance();
        });
        $this->output->progressFinish();

        if ($failCount > 0) {
            $percentage = round(($failCount / $total) * 100, 1);
            $this->line("Unable to fix $failCount ($percentage%) path activities");
        } else {
            $this->success("Resolved all paths <=> activities mappings");
        }
    }


    private function applyMappings()
    {
        $this->block("Setting marks activity_id...");

        $this->output->progressStart($this->mappings->count());
        foreach ($this->mappings as $pathId => $activityId) {
            DB::update("UPDATE marks
              SET markable_type = 'activity', markable_id = :activity_id
              WHERE path_id = :path_id", [
                "activity_id" => $activityId,
                "path_id" => $pathId
            ]);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }
}
