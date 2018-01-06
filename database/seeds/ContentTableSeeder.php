<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;

/**
 * Class ContentTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class ContentTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        // Content
        $this->seedFromFactory([
            "modelClass" => Country::class,
            "count" => random_int(2, 3)
        ]);
        $this->seedFromFactory([
            "modelClass" => Discipline::class,
            "count" => random_int(5, 10)
        ]);
        $this->seedFromFactory([
            "modelClass" => Class_::class,
            "count" => random_int(10, 15)
        ]);
        $this->seedFromFactory([
            "modelClass" => Chapter::class,
            "count" => random_int(50, 75)
        ]);
        $this->seedFromFactory([
            "modelClass" => Notion::class,
            "count" => random_int(100, 200)
        ]);
        $this->seedFromFactory([
            "modelClass" => Activity::class,
            "count" => random_int(300, 500)
        ]);
        $this->seedFromFactory([
            "modelClass" => Module::class,
            "subFactory" => "random",
            "count" => random_int(800, 1200)
        ]);

        $this->link(Class_::class, Discipline::class);
        $this->link(Chapter::class, Notion::class);
        $this->link(Notion::class, Activity::class);
        $this->link(Activity::class, Module::class);
    }
}
