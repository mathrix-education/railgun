<?php

namespace App\Models\Observers;

use App\Models\Comment;

/**
 * Class CommentObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class CommentObserver extends BaseObserver
{
    /**
     * @param Comment $comment
     *
     * @return bool
     */
    public function saving($comment)
    {
        if ($comment->isDirty('deleted_at')) {
            /** @var Comment $child */
            foreach ($comment->children as $child) {
                $child->forceDelete();
            }
        }

        return true;
    }


    /**
     * @param Comment $comment
     *
     * @return bool
     */
    public function deleting($comment)
    {
        /** @var Comment $child */
        foreach ($comment->children as $child) {
            $child->forceDelete();
        }

        return true;
    }
}