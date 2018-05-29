<?php
namespace App\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Models\Comment;
use Exception;

/**
 * Class CommentObserver
 *
 * @author Mathrix Team
 * @package App\Observers
 */
class CommentObserver extends BaseObserver
{
    /**
     * @param Comment $comment
     *
     * @return bool
     * @throws Http422ModelSaveException
     * @throws Exception
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
     * @return bool
     * @throws Http422ModelSaveException
     */
    public function deleting($comment)
    {
        /** @var Comment $child */
        foreach ($comment->children as $child) {
            $child->delete();
        }

        return true;
    }
}
