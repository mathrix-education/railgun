<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Category;
use App\Models\Post;
use App\Models\Quote;

/**
 * Class BlogTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class BlogTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Category::class,
            "count" => random_int(20, 40)
        ]);
        $this->seedFromFactory([
            "modelClass" => Post::class,
            "count" => random_int(20, 40)
        ]);
        $this->seedFromFactory([
            "modelClass" => Quote::class,
            "count" => random_int(50, 100)
        ]);
    }
}