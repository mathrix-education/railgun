<?php
namespace App\Console\Commands\SEO;

use App\Console\Commands\BaseCommand;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Notion;
use App\Models\Path;

/**
 * Class McqNoIndexCommand.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\SEO
 */
class McqNoIndexCommand extends BaseCommand
{
    protected $signature = "seo:mcq-no-index {--no-dry}";

    /**
     * @throws \App\Exceptions\Http422ModelSaveException
     * @throws \App\Exceptions\Http422ModelValidationException
     */
    public function handle()
    {
        $activityCounter = 0;
        $mcqCounter = 0;

        $chapters = Chapter::all();

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            $class = $chapter->class_;
            $country = $class->country;
            $discipline = $chapter->discipline;

            /** @var Notion $notion */
            foreach ($chapter->notions as $notion) {

                /** @var Activity $activity */
                foreach ($notion->activities as $activity) {
                    $char = ".";

                    if ($activity->modules->count() > 0) {
                        $module = $activity->modules[0];

                        if ($module->type === "mcq") {
                            $char = "*";
                            $path = Path::retrieve([
                                "country_id" => $country->id,
                                "class_id" => $class->id,
                                "discipline_id" => $discipline->id,
                                "chapter_id" => $chapter->id,
                                "notion_id" => $notion->id,
                                "activity_id" => $activity->id
                            ]);
                            $path->seo_index = false;

                            if ($this->hasOption("no-dry")) {
                                $path->save();
                            }

                            $mcqCounter++;
                        }
                    }

                    $activityCounter++;

                    if ($activityCounter % 60 === 0) {
                        $this->getOutput()->writeln($char);
                    } else {
                        $this->getOutput()->write($char);
                    }
                }
            }
        }

        $this->line("");
        $this->success("Patched $mcqCounter activities");
    }
}
