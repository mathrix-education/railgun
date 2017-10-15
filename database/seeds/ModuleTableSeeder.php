<?php
use App\Models\Activity;
use App\Models\Module;

class ModuleTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $activityIds = Activity::get(['id'])->pluck('id')->toArray();

        /** @var Activity $activity */
        foreach ($activityIds as $activityId) {
            $this->seedFromFactory(Module::class, [
                'subFactory' => 'random',
                'count' => random_int(1, 3),
                'callback' => function (Module $module) use ($activityId) {
                    $module->activities()->attach($activityId);
                },
                'loop' => $activityIds,
                'iterate' => $activityId
            ]);
        }
    }
}
