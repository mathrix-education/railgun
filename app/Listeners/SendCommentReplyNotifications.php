<?php

declare(strict_types=1);

namespace Railgun\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Railgun\Models\Comment;
use Railgun\Notifications\CommentReplyNotification;

class SendCommentReplyNotifications implements ShouldQueue
{
    public function handle(Comment $newComment): void
    {
        // Not optimal, we can use the following technique in the future: https://stackoverflow.com/a/36579929
        Comment::query()
            ->with(['user'])
            ->where('parent_id', '=', $newComment->parent_id)
            ->get()
            ->mapWithKeys(fn(Comment $comment) => [$newComment->user_id => $newComment])
            ->values()
            ->prepend(Comment::query()->with(['user'])->findOrFail($newComment->parent_id))
            ->each(static function (Comment $userComment) use ($newComment) {
                $user = $userComment->user;
                $userComment->unsetRelation('user');
                $user->notify(new CommentReplyNotification($userComment, $newComment));
            });
    }
}
