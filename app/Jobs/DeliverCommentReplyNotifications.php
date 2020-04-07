<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Railgun\Models\Comment;
use Railgun\Notifications\CommentReplyNotification;

/**
 * Deliver {@see CommentReplyNotification} to the previous comment authors of the thread.
 */
class DeliverCommentReplyNotifications extends Job
{
    private Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment->withoutRelations();
    }

    public function handle(): void
    {
        if ($this->comment->parent_id === null) {
            // Drop non-child comments
        }

        /**
         * Not optimal since we need to query the whole discussion, we can use the following technique in the future:
         *
         * @link https://stackoverflow.com/a/36579929
         */
        Comment::query()
            ->with(['user'])
            ->where('parent_id', '=', $this->comment->parent_id)
            ->where('user_id', '!=', $this->comment->user_id) // exclude the user who posted the comment
            ->get()
            ->prepend(Comment::query()->with(['user'])->findOrFail($this->comment->parent_id))
            ->sortBy('created_at')
            ->mapWithKeys(static fn(Comment $comment) => [$comment->user_id => $comment])
            ->values()
            ->each(function (Comment $userComment): void {
                $user = $userComment->user;
                // We are already in a queue, register save the notification right now
                $user->notifyNow(new CommentReplyNotification(
                    $userComment->withoutRelations(),
                    $this->comment->withoutRelations(),
                ));
            });
    }
}
