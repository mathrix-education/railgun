<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Category;
use App\Models\Post;
use App\Models\Quote;
use App\Models\Section;
use App\Models\Training;

/**
 * Class BlogTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
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
            "count" => random_int(20, 40)
        ]);
    }
}