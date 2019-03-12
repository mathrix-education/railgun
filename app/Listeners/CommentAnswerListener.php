<?php

namespace App\Listeners;

use App\Events\CommentAnswerEvent;
use App\Models\Comment;
use App\Notifications\CommentAnswerNotification;

/**
 * Class CommentAnswerListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Listeners
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
