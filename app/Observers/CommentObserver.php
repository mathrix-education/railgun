<?php

namespace App\Observers;

use App\Events\CommentAnswerEvent;
use App\Exceptions\Http422ModelSaveException;
use App\Models\Comment;
use Exception;

/**
 * Class CommentObserver.
 *
 * @author    Mathrix Back-End Team
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
 */
class CommentObserver extends BaseObserver
{
    /**
     * @param Comment $comment
     */
    public function saved($comment)
    {
        if ($comment->parent_id !== null) {
            event(new CommentAnswerEvent($comment));
        }
    }


    /**
     * @param Comment $comment
     *
     * @return bool
     * @throws Exception
     */
    public function deleting($comment)
    {
        $children = Comment::withTrashed()
            ->where("parent_id", "=", $comment->id)
            ->get();

        /** @var Comment $child */
        foreach ($children as $child) {
            if ($comment->isForceDeleting()) {
                $child->forceDelete();
            } else {
                $child->delete();
            }
        }

        return true;
    }
}
