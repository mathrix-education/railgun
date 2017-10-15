<?php

namespace App\Models\Observers;

use App\Models\Comment;
use App\Models\Post;

/**
 * Class PostObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
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
        // Ensure the title is present
        if (!$post->validate(['title' => 'required|max:255'])) {
            $post->setResponse($post->http422Validation([
                'message' => $post->errors()
            ]));

            return false;
        }

        // Sluggify if needed
        if (
            empty($post->slug) ||
            ($post->isDirty('title') && !$post->isDirty('slug'))
        ) {
            $post->slug = str_slug($post->title);
        }

        return parent::saving($post);
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