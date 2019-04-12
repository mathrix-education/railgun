<?php

namespace App\Console\Commands\Pyramid;

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
class PyramidMarksModuleCommand extends BaseCommand
{
    protected $signature = "pyramid:marks:modules";
    /** @var Collection */
    private $mappingsModules;


    public function handle()
    {
        $this->mappingsModules = new Collection();

        $this->makeMappings();
        $this->applyMappings();
    }


    private function resolveModule($path)
    {
        if (!empty($this->mappingsModules[$path->id])) {
            return true;
        }

        $resolved = Module::query()
            ->where([
                ["content", "=", $path->content],
                ["questions", "=", $path->questions],
                ["corrections", "=", $path->corrections],
                ["solutions", "=", $path->solutions]
            ])
            ->whereHas("activity.notion.chapter", function (Builder $query) use ($path) {
                return $query->where("id", "=", $path->chapter_id);
            })
            ->first();

        if ($resolved instanceof Module) {
            $this->mappingsModules->offsetSet($path->id, $resolved->id);
            return true;
        } else {
            return false;
        }
    }


    private function makeMappings()
    {
        $this->block("Building paths <=> modules mappings...");
        $data = DB::table("paths")
            ->join("activities", "paths.activity_id", "=", "activities.id")
            ->join("modules", "paths.activity_id", "=", "modules.activity_id")
            ->whereNotNull("module_id")
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
            ->whereNotNull("module_id")
            ->count();
        $failCount = 0;

        $this->output->progressStart($data->count());
        $data->each(function ($path) use (&$failCount) {
            if (!$this->resolveModule($path)) {
                $failCount++;
            }
            $this->output->progressAdvance();
        });
        $this->output->progressFinish();

        if ($failCount > 0) {
            $percentage = round(($failCount / $total) * 100, 1);
            $this->line("Unable to fix $failCount ($percentage%) path modules");
        } else {
            $this->success("Resolved all paths <=> modules mappings");
        }
    }


    private function applyMappings()
    {
        $this->block("Setting marks module_id...");

        $this->output->progressStart($this->mappingsModules->count());
        foreach ($this->mappingsModules as $pathId => $moduleId) {
            DB::update("UPDATE marks
              SET markable_type = 'module', markable_id = :module_id
              WHERE path_id = :path_id", [
                "module_id" => $moduleId,
                "path_id" => $pathId
            ]);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }
}
