<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class PostObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class PostObserver extends BaseObserver
{
    /**
     * @param Post $post
     *
     * @return bool
     */
    public function saving($post)
    {
        // By default set the post user_id to the current user id.
        if (empty($post->user_id)) {
            /** @var User $user */
            $user = Auth::user();
            $post->user_id = $user->id;
        }

        return true;
    }


    /**
     * @param Post $post
     *
     * @return bool
     */
    public function deleting($post)
    {
        // Force delete linked comments and their children
        $post->comments()->each(function (Comment $comment) {
            $comment->forceDelete();
        });

        return true;
    }
}
