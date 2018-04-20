<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Comment;

/**
 * Class CommentTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class CommentTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Comment::class,
            "count" => random_int(50, 180)
        ]);
    }
}
