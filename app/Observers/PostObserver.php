<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PostObserver extends BaseObserver
{
    public function saving(Post $post) : bool
    {
        // By default set the post user_id to the current user id.
        if (empty($post->user_id)) {
            /** @var User $user */
            $user          = Auth::user();
            $post->user_id = $user->id;
        }

        return true;
    }

    public function deleting(Post $post) : bool
    {
        // Force delete linked comments and their children
        $post->comments()->each(static function (Comment $comment) : void {
            $comment->forceDelete();
        });

        return true;
    }
}
