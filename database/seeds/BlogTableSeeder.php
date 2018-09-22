<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Category;
use App\Models\Module;
use App\Models\Post;
use App\Models\Quote;

/**
 * Class BlogTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class BlogTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Exception
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Category::class,
            "count" => random_int(5, 8),
        ]);
        $this->seedFromFactory([
            "modelClass" => Module::class,
            "subFactory" => "course",
            "count" => 40,
        ]);
        $this->seedFromFactory([
            "modelClass" => Post::class,
            "count" => random_int(10, 20),
        ]);
        // Seed categories without posts
        $this->seedFromFactory([
            "modelClass" => Category::class,
            "count" => random_int(5, 8),
        ]);
        $this->seedFromFactory([
            "modelClass" => Quote::class,
            "count" => random_int(10, 20),
        ]);
    }
}
