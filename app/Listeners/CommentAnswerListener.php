<?php
namespace App\Listeners;

use App\Events\CommentAnswerEvent;
use App\Notifications\CommentAnswerNotification;

/**
 * Class CommentAnswerListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class CommentAnswerListener extends BaseListener
{
    public function handle(CommentAnswerEvent $event)
    {
        $childComment = $event->comment;
        $parentComment = $childComment->parent;
        $parentAuthor = $parentComment->user;
        $parentAuthor->notify(new CommentAnswerNotification($childComment, $childComment->user));
    }
}
