<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidOrphansCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class Pyramid04OrphansCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "pyramid:orphans";

    public function handle()
    {
        $this->cell("Searching for orphans");
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
            $this->cell("No orphan $childPlural found.", false);

            return;
        }

        $this->line("Found {$orphanModels->count()} orphan $childPlural");

        $brokenModels = DB::table($table)
            ->selectRaw("$childKey, MIN($parentKey)")
            ->whereIn($childKey, $orphanModels->pluck("id"))
            ->groupBy($childKey)
            ->get();

        if ($brokenModels->count() === 0) {
            $this->cell("No $childPlural need to be fixed.", false);

            return;
        }

        $this->cell("Need to fix {$brokenModels->count()} $childPlural", false);

        $this->progressStart($brokenModels->count());
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

            $this->progressAdvance();
        }
        $this->progressFinish();

        $this->cell("Fixed {$brokenModels->count()} $childPlural", false);
    }
}
