<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\CommentAnswerNotification;
use Illuminate\Console\Command;

/**
 * Class Dry.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class Dry extends Command
{
    protected $signature = "dry";

    public function handle()
    {
        $user = User::random();
        $user2 = User::random();
        $comment = Comment::random();

        $user->notify(new CommentAnswerNotification($comment, $user2));
    }
}
