<?php
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use Mathrix\Lumen\Database\BaseTableSeeder;

class ContentSeeder extends BaseTableSeeder
{
    public function run()
    {
        $classes = Class_::with('disciplines')->get();

        foreach ($classes as $class) {
            /** @var Discipline $discipline */
            foreach ($class->disciplines as $discipline) {
                factory(Chapter::class, random_int(5, 7))->create([
                    'class_id' => $class->id,
                    'discipline_id' => $discipline->id
                ])->each(function (Chapter $chapter) {
                    factory(Notion::class, random_int(2, 3))
                        ->create([
                            'chapter_id' => $chapter->id,
                        ])
                        ->each(function (Notion $notion) {
                            $activityCount = random_int(1, 3);

                            for ($i = 0; $i < $activityCount; $i++) {
                                $types = ['course', 'exercise', 'test', 'video'];
                                $type = $types[random_int(0, 3)];

                                factory(Activity::class, $type, 1)
                                    ->create()
                                    ->each(function (Activity $activity) use ($notion, $type) {
                                        $notion->activities()->attach($activity->id);

                                        if ($type == 'exercise') {
                                            factory(Module::class, random_int(2, 3))
                                                ->create()
                                                ->each(function (Module $module) use ($activity) {
                                                    $activity->modules()->attach($module->id);
                                                });
                                        }
                                    });
                            }
                        });
                });
            }
        }
    }
}