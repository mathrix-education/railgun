<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Post;

/**
 * Class PostObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
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
        // Sluggify if needed
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
        /** @var Comment $comment */
        foreach ($post->comments as $comment) {
            $comment->forceDelete();
        }

        return true;
    }
}
