<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Railgun\Models\Comment;
use Railgun\Notifications\CommentReplyNotification;

class DeliverCommentReplyNotifications extends Job
{
    private Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function handle(): void
    {
        /**
         * Not optimal since we need to query the whole discussion, we can use the following technique in the future:
         *
         * @link https://stackoverflow.com/a/36579929
         */
        Comment::query()
            ->with(['user'])
            ->where('parent_id', '=', $this->comment->parent_id)
            ->get()
            ->mapWithKeys(fn(Comment $comment) => [$this->comment->user_id => $this->comment])
            ->values()
            ->prepend(Comment::query()->with(['user'])->findOrFail($this->comment->parent_id))
            ->each(function (Comment $userComment) {
                $user = $userComment->user;
                $userComment->unsetRelation('user');

                // We are already in a queue, register save the notification right now
                $user->notifyNow(new CommentReplyNotification($userComment, $this->comment));
            });
    }
}
