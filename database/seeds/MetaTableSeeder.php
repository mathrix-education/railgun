<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Meta;
use App\Models\Notion;
use App\Models\User;

/**
 * Class MetaTableSeeder.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 4.0.3
 *
 * @copyright Mathrix Education SA
 */
class MetaTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Exception
     */
    public function run()
    {
        $registered = User::where(["type" => "registered"])->get();
        $metas = [];

        /** @var User $user */
        foreach ($registered as $user) {
            $metaCount = random_int(0, 10);

            for ($i = 0; $i < $metaCount; ++$i) {
                /** @var Class_ $class_ */
                $class_ = $user->class_;
                /** @var Discipline $discipline */
                $discipline = $user->class_->disciplines()->inRandomOrder()->first();
                /** @var Chapter $chapter */
                $chapter = $discipline->chapters()->inRandomOrder()->first();
                /** @var Notion $notion */
                $notion = $chapter->notions()->inRandomOrder()->first();
                /** @var Activity $activity */
                $activity = $notion->activities()->inRandomOrder()->first();

                $statusId = random_int(0, 2);

                $metas[] = [
                    "user_id" => $user->id,
                    "country_id" => $user->class_->country->id,
                    "class_id" => $class_->id,
                    "discipline_id" => $discipline->id,
                    "chapter_id" => $chapter->id,
                    "notion_id" => $notion->id,
                    "activity_id" => $activity->id,
                    "favorite" => (bool) random_int(0, 1),
                    "completed" => $statusId === 0,
                    "to_revise" => $statusId === 1,
                ];
            }
        }

        $this->seedFromArray($metas, Meta::class, true);
    }
}
