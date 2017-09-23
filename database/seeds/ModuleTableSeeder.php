<?php
use App\Models\Activity;
use App\Models\Module;
use Mathrix\Lumen\Database\BaseTableSeeder;

class ModuleTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> modules from factory');

        $activities = Activity::get(['id']);

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            factory(Module::class, 'random', random_int(1, 3))->create()->each(function (Module $module) use ($activity) {
                $activity->modules()->attach($module->id);
            });
        }
        $this->println('<info>Seeded:</info>  modules from factory');
    }
}
