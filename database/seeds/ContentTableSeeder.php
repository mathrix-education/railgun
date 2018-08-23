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
 * Class ContentTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
class ContentTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Exception
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        // Content
        $this->seedFromJson([
            "modelClass" => Country::class,
        ]);
        $this->seedFromJson([
            "modelClass" => Class_::class,
            "table" => "classes",
        ]);
        $this->seedFromJson([
            "modelClass" => Discipline::class,
        ]);
        $this->seedFromFactory([
            "modelClass" => Chapter::class,
            "count" => random_int(50, 70),
        ]);
        $this->seedFromFactory([
            "modelClass" => Notion::class,
            "count" => random_int(100, 200),
        ]);
        $this->seedFromFactory([
            "modelClass" => Activity::class,
            "count" => random_int(150, 300),
        ]);
        $this->seedFromFactory([
            "modelClass" => Module::class,
            "subFactory" => "random",
            "count" => random_int(200, 350),
        ]);

        $this->link(Class_::class, Discipline::class);
        $this->link(Chapter::class, Notion::class);
        $this->link(Notion::class, Activity::class);
        $this->link(Activity::class, Module::class);
    }
}
