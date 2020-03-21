<?php

declare(strict_types=1);

namespace Railgun\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Railgun\Models\Comment;

/**
 * Sent when a new comment has been posted in a "discussion".
 */
class CommentReplyNotification extends Notification implements ShouldQueue
{
    private Comment $userComment;
    private Comment $newComment;

    public function __construct(Comment $userComment, Comment $newComment)
    {
        $this->userComment = $userComment;
        $this->newComment  = $newComment;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toDatabase(): array
    {
        return [
            'userComment' => $this->userComment,
            'newComment'  => $this->newComment,
        ];
    }
}
