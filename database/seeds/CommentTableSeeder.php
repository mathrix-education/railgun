<?php

use App\Events\CommentAnswerEvent;
use App\Models\Comment;
use App\Models\Vote;

/**
 * Class CommentTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     1.0.1
 * @copyright Mathrix Education SA
 */
class CommentTableSeeder extends BaseTableSeeder
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->seedFromFactory(Comment::class, random_int(50, 80));
        $this->seedFromSubFactory(Comment::class, "child", random_int(150, 200));
        $this->seedFromFactory(Vote::class, random_int(250, 300));
    }
}
