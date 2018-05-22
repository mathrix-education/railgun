<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Section;
use App\Models\Training;

/**
 * Class BlogTableSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class TrainingTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Exception
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Training::class,
            "count" => random_int(30, 40)
        ]);
        $this->seedFromFactory([
            "modelClass" => Section::class,
            "count" => random_int(100, 300)
        ]);
        $this->seedFromFactory([
            "modelClass" => Lesson::class,
            "count" => random_int(300, 500)
        ]);
        $this->seedFromFactory([
            "modelClass" => Purchase::class,
            "count" => random_int(200, 500)
        ]);
    }
}
