<?php
namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Console\Command;

/**
 * Class Road
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package   App\Console\Commands
 * @since
 * @copyright Mathrix Education SA
 */
class Road extends Command
{
    protected $signature = "road {moduleId}";

    public function handle()
    {
        $moduleId = $this->argument("moduleId");
        $module = Module::findOrFail($moduleId);
        /** @var Activity $activity */
        $activity = $module->activities()->firstOrFail();
        /** @var Notion $notion */
        $notion = $activity->notions()->firstOrFail();
        /** @var Chapter $chapter */
        $chapter = $notion->chapters()->firstOrFail();
        /** @var Class_ $class */
        $class = $chapter->class_;
        /** @var Discipline $discipline */
        $discipline = $chapter->discipline;

        $models = [
            "discipline" => $discipline,
            "class" => $class,
            "chapter" => $chapter,
            "notion" => $notion,
            "activity" => $activity,
            "module" => $module
        ];

        $data = [];

        foreach ($models as $type => $model) {
            $data[] = [
                "type" => $type,
                "id" => $model->id,
                "name" => $model->name,
                "visible" => $model->visible ?? null
            ];
        }

        $this->table(["type", "id", "name", "visible"], $data);
    }
}
