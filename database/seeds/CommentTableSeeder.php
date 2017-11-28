<?php
use App\Models\Comment;

/**
 * Class CommentTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.1
 * @copyright Mathrix
 */
class CommentTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Comment::class,
            "count" => random_int(200, 400)
        ]);
    }
}