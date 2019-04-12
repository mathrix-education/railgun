<?php

namespace App\Console\Commands\Pyramid;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidMigrateCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class PyramidMigrateCommand extends BaseCommand
{
    use CopyTrait;

    protected $signature = "pyramid:migrate";


    public function handle()
    {
        $this->call("cache:clear");
        $this->migrate("Activity", "Module", "activity_module");
        $this->migrate("Notion", "Activity", "activity_notion");
        $this->migrate("Chapter", "Notion", "chapter_notion");
    }


    public function migrate($parent, $child, $table)
    {
        $parentKey = mb_strtolower($parent) . "_id";
        $parentPlural = Str::plural(mb_strtolower($parent));
        $childKey = mb_strtolower($child) . "_id";
        $childPlural = Str::plural(mb_strtolower($child));

        $this->line("Fetching $parentPlural <=> $childPlural mappings...");

        $query = "SELECT $parentKey, $table.$childKey, `order`, duplicated
        FROM $table
          INNER JOIN
          (SELECT $childKey, CASE WHEN COUNT(id) > 1 THEN 1 ELSE 0 END AS duplicated
          FROM $table
          GROUP BY $childKey) dup ON $table.$childKey = dup.$childKey
        ORDER BY $table.$childKey, $parentKey";

        $mappings = collect(DB::select($query));
        $processed = new Collection();

        $this->line("{$mappings->count()} $childPlural to migrate.");
        $this->line("Migrating $table...");

        $this->output->progressStart($mappings->count());

        foreach ($mappings as $line) {
            $parentId = $line->{$parentKey};
            $childId = $line->{$childKey};
            $order = $line->order;
            $hasDuplicates = $line->duplicated == 1;

            if (!$hasDuplicates || $processed->search($childId) !== false) {
                DB::table($childPlural)
                    ->where("id", "=", $childId)
                    ->update([
                        $parentKey => $parentId,
                        "order" => $order
                    ]);

                $processed->push($childId);
            } else {
                $method = "copy$child";
                $this->$method($line->{$childKey}, $line->{$parentKey}, $line->order);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->success("Migrated $table");
    }
}
