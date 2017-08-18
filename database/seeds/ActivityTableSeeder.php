<?php
use App\Models\Activity;
use App\Models\Notion;
use Mathrix\Lumen\Database\BaseTableSeeder;

class ActivityTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<info>Seeding</info> activities from factory');

        $notions = Notion::get(['id']);

        /** @var Notion $notion */
        foreach ($notions as $notion) {
            factory(Activity::class, random_int(1, 3))->create()->each(function(Activity $activity) use ($notion) {
                $notion->activities()->attach($activity->id);
            });
        }
    }
}