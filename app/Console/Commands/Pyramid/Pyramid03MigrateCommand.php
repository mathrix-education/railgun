<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class Pyramid2Command.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc3
 */
class Pyramid03MigrateCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "pyramid:migrate";


    public function handle()
    {
        $this->handleDuplicates("chapter", "notion");
        $activityMappings = $this->handleDuplicates("notion", "activity");
        $this->handleDuplicates("activity", "module");
        $this->duplicateModules($activityMappings);
    }


    /**
     * Strip data by removing its id and add created_at and updated_at fields if needed.
     *
     * @param $data
     *
     * @return array
     */
    private function strip($data): array
    {
        if ($data instanceof \stdClass) {
            $data = (array)$data;
        }

        if (isset($data["id"])) {
            unset($data["id"]);
        }

        if ($data["created_at"] === null) {
            $data["created_at"] = Carbon::now();
        }
        if ($data["updated_at"] === null) {
            $data["updated_at"] = Carbon::now();
        }

        return $data;
    }


    public function handleDuplicates($parent, $child)
    {
        $parentKey = "{$parent}_id";
        //$parents = Str::plural($parent);
        $childKey = "{$child}_id";
        $children = Str::plural($child);
        $table = strcmp($parent, $child) < 0 ? "{$parent}_{$child}" : "{$child}_{$parent}";


        $dataset = DB::table($table)
            ->orderByRaw("$childKey ASC, $parentKey ASC")
            ->get();

        $groups = new Collection();
        foreach ($dataset as $line) {
            if (isset($groups[$line->{$childKey}])) {
                $groups[$line->{$childKey}]->push($line);
            } else {
                $groups[$line->{$childKey}] = new Collection([$line]);
            }
        }
        /** @var Collection $mappings The activity mappings; key is the activity id, value its parent id. */
        $mappings = new Collection();

        $this->cell("Cloning $children");
        $this->progressStart($groups->count());
        foreach ($groups as $childId => $group) {
            // Set first group data, copy the others
            $firstGroup = $group->shift();
            DB::table($children)->where("id", "=", $childId)
                ->update([
                    $parentKey => $firstGroup->{$parentKey},
                    "order" => $firstGroup->order,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);

            $childObject = DB::table($children)
                ->where("id", "=", $childId)
                ->first();
            $childData = $this->strip($childObject);

            foreach ($group as $line) {
                /*
                 * We need to copy the activity $activityId with:
                 * notion_id = $NALine->notion_id
                 * order = $NALine->order
                 */
                $insertData = array_merge($childData, [
                    $parentKey => $line->{$parentKey},
                    "order" => $line->order
                ]);

                $insertedId = DB::table($children)->insertGetId($insertData);
                $mappings[$insertedId] = $childId;
            }
            $this->progressAdvance();
        }
        $this->progressFinish();

        return $mappings;
    }


    public function duplicateModules(Collection $activityMappings)
    {
        $data = new Collection();

        $this->cell("Analysing activity mappings");
        $this->progressStart($activityMappings->count());
        foreach ($activityMappings as $activityId => $sourceId) {
            $modules = DB::table("modules")
                ->where("activity_id", "=", $sourceId)
                ->orderBy("order")
                ->get();

            foreach ($modules as $module) {
                $data->push(array_merge($this->strip($module), ["activity_id" => $activityId]));
            }
            $this->progressAdvance();
        }
        $this->progressFinish();

        $chunks = $data->chunk(100);

        $this->cell("Saving chunks");
        $this->progressStart($data->count());
        /** @var Collection $chunk */
        foreach ($chunks as $chunk) {
            DB::table("modules")->insert($chunk->toArray());
            $this->progressAdvance($chunk->count());
        }
        $this->progressFinish();
    }
}
