<?php
namespace Database\seeds;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Notion;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class MarksTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   Database\seeds
 */
class MarksTableSeeder extends BaseTableSeeder
{

    /**
     * @throws \Exception
     */
    public function run()
    {
        $registered = User::where(["type" => "registered"])
            ->get();
        $marks = [];
        $marksSignatures = [];

        /** @var User $user */
        foreach ($registered as $user) {
            $metaCount = random_int(30, 50);

            for ($i = 0; $i < $metaCount; ++$i) {
                /** @var Class_ $class_ */
                $class_ = $user->class_;
                /** @var Discipline $discipline */
                $discipline = $user->class_->disciplines()
                    ->has("chapters")
                    ->inRandomOrder()
                    ->firstOrFail();
                /** @var Chapter $chapter */
                $chapter = $discipline->chapters()
                    ->inRandomOrder()
                    ->firstOrFail();
                /** @var Notion $notion */
                $notion = $chapter->notions()
                    ->inRandomOrder()
                    ->firstOrFail();
                /** @var Activity $activity */
                $activity = $notion->activities()
                    ->inRandomOrder()
                    ->firstOrFail();

                // Register mark in signatures to avoid marks duplicates
                $marksSignature = "{$user->id},{$activity->id}";

                if (in_array($marksSignature, $marksSignatures)) {
                    continue;
                }

                $marksSignatures[] = $marksSignature;

                $statusId = random_int(0, 2);
                $statuses = ["view", "to_revise", "completed"];

                $path = \App\Models\Path::retrieve([
                    "country_id" => $user->class_->country->id,
                    "class_id" => $class_->id,
                    "discipline_id" => $discipline->id,
                    "chapter_id" => $chapter->id,
                    "notion_id" => $notion->id,
                    "activity_id" => $activity->id,
                    "module_id" => null
                ]);

                $marks[] = [
                    "path_id" => $path->id,
                    "user_id" => $user->id,
                    "status" => $statuses[$statusId],
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
            }
        }

        $this->seedFromArray($marks, "marks");
    }
}
