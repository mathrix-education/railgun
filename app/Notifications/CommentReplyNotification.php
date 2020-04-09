<?php

declare(strict_types=1);

namespace Railgun\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Railgun\Models\Comment;
use function serialize;

/**
 * Sent when a new comment has been posted in a "discussion".
 */
class CommentReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Comment $userComment;
    private Comment $newComment;

    public function __construct(Comment $userComment, Comment $newComment)
    {
        $this->userComment = $userComment;
        $this->newComment  = $newComment;
    }

    /**
     * @return string[] The notification routes.
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * @return mixed[] The database representable of the notification.
     */
    public function toDatabase(): array
    {
        return [
            'user_comment' => serialize($this->userComment),
            'new_comment'  => serialize($this->newComment),
        ];
    }
}
