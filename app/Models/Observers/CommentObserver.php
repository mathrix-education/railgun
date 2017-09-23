<?php

namespace App\Models\Observers;

use App\Models\Comment;
use Mathrix\Lumen\Observers\BaseObserver;

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

        return parent::saving($comment);
    }

    /**
     * @param Comment $comment
     *
     * é"&é&
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