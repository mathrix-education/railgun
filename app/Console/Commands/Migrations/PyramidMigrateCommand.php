<?php

namespace App\Console\Commands\Migrations;

use App\Models\Activity;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidMigrateCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidMigrateCommand extends BaseCommand
{
    use CopyTrait;

    protected $signature = "pyramid:migrate";


    public function handle()
    {
        $this->call("cache:clear");
        $this->initialSet();
        $this->migrate("Activity", "Module", "activity_module");
        $this->migrate("Notion", "Activity", "activity_notion");
        $this->migrate("Chapter", "Notion", "chapter_notion");
    }


    public function initialSet()
    {
        $this->line("Setting initial ids...");
        $notions = Notion::all();
        $activities = Activity::all();
        $modules = Module::all();

        $this->output->progressStart($notions->count() + $activities->count() + $modules->count());
        $notions->each(function (Notion $notion) {
            $query = $notion->chapters()->withPivot(["order"]);
            if ($query->count() > 0) {
                $pivot = $query->first()->pivot;
                $notion->order = $pivot->order;
                $notion->chapter_id = $pivot->chapter_id;
                $notion->save();
            }
            $this->output->progressAdvance();
        });
        $activities->each(function (Activity $activity) {
            $query = $activity->notions()->withPivot(["order"]);
            if ($query->count() > 0) {
                $pivot = $query->first()->pivot;
                $activity->order = $pivot->order;
                $activity->notion_id = $pivot->notion_id;
                $activity->save();
                $this->output->progressAdvance();
            }
        });
        $modules->each(function (Module $module) {
            $query = $module->activities()->withPivot(["order"]);
            if ($query->count() > 0) {
                $pivot = $query->first()->pivot;
                $module->order = $pivot->order;
                $module->activity_id = $pivot->activity_id;
                $module->save();
                $this->output->progressAdvance();
            }
        });
        $this->output->progressFinish();
        $this->success("Done.");
    }


    public function migrate($parent, $child, $table)
    {
        $parentKey = mb_strtolower($parent) . "_id";
        $parentPlural = Str::plural(mb_strtolower($parent));
        $childKey = mb_strtolower($child) . "_id";
        $childPlural = Str::plural(mb_strtolower($child));

        $this->line("Fetching $parentPlural <=> $childPlural mappings...");
        $mappings = collect(DB::select("SELECT * FROM $table"));
        $this->line("{$mappings->count()} $childPlural to migrate.");


        $this->line("Analysing mappings...");
        $mappings = $mappings->map(function ($line) use ($parentKey, $childKey, $mappings) {
            if (!empty($line->duplicated) && $line->duplicated === true) {
                return $line;
            }

            // Mark duplicates
            $duplicates = $mappings->where($childKey, "=", $line->{$childKey});
            if ($duplicates->count() > 1) {
                $duplicates->each(function ($duplicate, $duplicateKey) use ($mappings, $line, $parentKey) {
                    if ($duplicate->{$parentKey} !== $line->{$parentKey}) {
                        $duplicatedLine = $mappings->offsetGet($duplicateKey);
                        $duplicatedLine->duplicated = true;
                        $mappings->offsetSet($duplicateKey, $duplicatedLine);
                    }
                });
            }

            $line->duplicated = false;

            return $line;
        });
        $toClone = $mappings->where("duplicated", "=", true);
        $this->success("{$toClone->count()} $childPlural to clone");


        $this->line("Cloning $childPlural...");
        $this->output->progressStart($toClone->count());
        $toClone->each(function ($toCloneLine) use ($child, $childKey, $parentKey) {
            $method = "copy$child";
            $this->$method($toCloneLine->{$childKey}, $toCloneLine->{$parentKey}, $toCloneLine->order);
            $this->output->progressAdvance();
        });
        $this->output->progressFinish();
        $this->success("Done.");
    }
}
