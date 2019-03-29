<?php

namespace App\Listeners;

use App\Events\CommentAnswerEvent;
use App\Models\Comment;
use App\Notifications\CommentAnswerNotification;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class CommentAnswerListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class CommentAnswerListener extends BaseListener
{
    public function handle(CommentAnswerEvent $event)
    {
        $childComment = $event->comment;
        $parentComment = $childComment->parent;

        // Notify the parent comment author
        $parentAuthor = $parentComment->user;
        $parentAuthor->notify(new CommentAnswerNotification($childComment, $childComment->user));

        // Notify the comment children authors who posted before the event's comment
        $childrenBefore = $parentComment->children()
            ->where("created_at", "<", $childComment->created_at)
            ->get();

        /** @var Comment $comment */
        foreach ($childrenBefore as $comment) {
            $comment->user->notify(new CommentAnswerNotification($childComment, $childComment->user));
        }
    }
}
