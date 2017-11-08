<?php
use App\Models\Activity;
use App\Models\Notion;

/**
 * Class ActivityTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class ActivityTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $notionIds = Notion::get(["id"])->pluck("id")->toArray();

        /** @var Notion $notion */
        foreach ($notionIds as $notionId) {
            $this->seedFromFactory(Activity::class, [
                "count" => random_int(1, 3),
                "callback" => function (Activity $activity) use ($notionId) {
                    $activity->notions()->attach($notionId);
                },
                "loop" => $notionIds,
                "iterate" => $notionId
            ]);
        }
    }
}