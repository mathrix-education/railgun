<?php

namespace App\Console\Commands\Pyramid;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidOrphansCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidOrphansCommand extends BaseCommand
{
    protected $signature = "pyramid:orphans";


    public function handle()
    {
        $this->orphans("activity", "module");
        $this->orphans("notion", "activity");
        $this->orphans("chapter", "notion");
    }


    /**
     * Fix orphan modules if needed
     *
     * @param string $parent
     * @param string $child
     */
    private function orphans(string $parent, string $child)
    {
        // Generate strings
        $parentSingular = Str::singular($parent);
        // $parentPlural = Str::plural($parentSingular); // Unused
        $parentKey = "{$parentSingular}_id";
        $childSingular = Str::singular($child);
        $childPlural = Str::plural($childSingular);
        $childKey = "{$childSingular}_id";

        $table = strcmp($parentSingular, $childSingular) < 0
            ? "{$parentSingular}_{$childSingular}"
            : "{$childSingular}_{$parentSingular}";

        $orphanModels = DB::table($childPlural)
            ->select("id")
            ->whereNull($parentKey)
            ->get();

        if ($orphanModels->count() === 0) {
            $this->success("No orphan $childPlural found.");

            return;
        }

        $this->line("Found {$orphanModels->count()} orphan $childPlural");

        $brokenModels = DB::table($table)
            ->selectRaw("$childKey, MIN($parentKey)")
            ->whereIn($childKey, $orphanModels->pluck("id"))
            ->groupBy($childKey)
            ->get();

        if ($brokenModels->count() === 0) {
            $this->success("No $childPlural need to be fixed.");

            return;
        }

        $this->line("Need to fix {$brokenModels->count()} $childPlural");

        $this->output->progressStart($brokenModels->count());
        foreach ($brokenModels as $brokenModel) {
            $childId = $brokenModel->{$childKey};

            $line = DB::table($table)
                ->select($parentKey)
                ->where($childKey, "=", $childId)
                ->orderBy($childKey)
                ->limit(1)
                ->first();

            $parentId = $line->{$parentKey};

            DB::table($childPlural)
                ->where("id", "=", $childId)
                ->update([$parentKey => $parentId]);

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->success("Fixed {$brokenModels->count()} $childPlural");
    }


    private function makeReport()
    {

    }
}
