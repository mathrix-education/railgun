<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Comment;
use App\Models\Vote;

/**
 * Class CommentTableSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 1.0.1
 * @copyright Mathrix Education SA
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
        $this->seedFromFactory([
            "modelClass" => Vote::class,
            "count" => random_int(300, 500)
        ]);
    }
}
