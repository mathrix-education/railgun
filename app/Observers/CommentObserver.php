<?php

namespace App\Observers;

use App\Events\CommentAnswerEvent;
use App\Models\Comment;
use App\Models\Vote;
use Exception;

/**
 * Class CommentObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
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

        if ($comment->isForceDeleting()) {
            $comment->votes()->each(function (Vote $vote) {
                $vote->delete();
            });
        }

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
