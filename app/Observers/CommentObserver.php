<?php

namespace App\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Models\Comment;
use Exception;

/**
 * Class CommentObserver.
 *
 * @author Mathrix Team
 */
class CommentObserver extends BaseObserver
{
    /**
     * @param Comment $comment
     *
     * @throws Http422ModelSaveException
     * @throws Exception
     *
     * @return bool
     */
    public function saving($comment)
    {
        if ($comment->isDirty("deleted_at")) {
            /** @var Comment $child */
            foreach ($comment->children as $child) {
                $child->delete();
            }
        }

        return true;
    }

    /**
     * @param Comment $comment
     *
     * @throws Http422ModelSaveException
     *
     * @return bool
     */
    public function deleting($comment)
    {
        /** @var Comment $child */
        foreach ($comment->children as $child) {
            if ($comment->isForceDeleting()) {
                $child->forceDelete();
            } else {
                $child->delete();
            }
        }

        return true;
    }
}
