<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Lesson;

use App\Models\Section;
use App\Models\Training;

/**
 * Class BlogTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class TrainingTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Training::class,
            "count" => random_int(20, 40)
        ]);
        $this->seedFromFactory([
            "modelClass" => Section::class,
            "count" => random_int(60, 120)
        ]);
        $this->seedFromFactory([
            "modelClass" => Lesson::class,
            "count" => random_int(180, 360)
        ]);
    }
}
