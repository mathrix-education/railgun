<?php

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
        $this->seedFromFactory(Comment::class, random_int(50, 180));
        $this->seedFromSubFactory(Comment::class, "child", random_int(100, 200));
        $this->seedFromFactory(Vote::class, random_int(250, 300));
    }
}
