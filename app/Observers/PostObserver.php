<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Post;

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
        // Set the slug if needed
        if (empty($post->slug) ||
            ($post->isDirty("title") && !$post->isDirty("slug"))) {
            $post->slug = str_slug($post->title);
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
