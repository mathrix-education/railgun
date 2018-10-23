<?php
namespace App\Console\Commands\SEO;

use App\Models\Class_;
use App\Models\Meta;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Class GenerateIndex.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 */
class GenerateIndex extends Command
{
    protected $signature = "seo:generate:index";

    /**
     * @throws \App\Exceptions\Http422ModelSaveException
     * @throws \App\Exceptions\Http422ModelValidationException
     */
    public function handle()
    {
        /** @var Collection|Meta[] $metas */
        $metas = new Collection();
        $classes = Class_::whereIn("slug", ["3eme", "2nde", "1ere-s", "terminale-s"])->get();

        /** @var Class_ $class */
        foreach ($classes as $class) {
            foreach ($class->chapters as $chapter) {
                foreach ($chapter->notions as $notion) {
                    foreach ($notion->activities as $activity) {
                        if ($activity->modules->isEmpty()) {
                            continue;
                        }

                        $module = $activity->modules[0];
                        $discipline = $chapter->discipline;

                        $metas->push(new Meta([
                            "favorite" => false,
                            "to_revise" => false,
                            "completed" => false,
                            "seo_index" => true,
                            "user_id" => null,
                            "country_id" => 1,
                            "class_id" => $class->id,
                            "discipline_id" => $discipline->id,
                            "chapter_id" => $chapter->id,
                            "notion_id" => $notion->id,
                            "activity_id" => $activity->id,
                            "module_id" => $module->id
                        ]));
                    }
                }
            }
        }

        $this->output->progressStart($metas->count());
        foreach ($metas as $meta) {
            $meta->save();
            $this->output->progressAdvance(1);
        }
        $this->output->progressFinish();
    }
}
