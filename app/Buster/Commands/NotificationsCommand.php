<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Eloquent\Collection;
use Railgun\Jobs\DeliverCommentReplyNotifications;
use Railgun\Models\Comment;
use function dispatch_now;

class NotificationsCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:notifications';

    public function handle(): void
    {
        $this->title('Generating comments notifications');

        Comment::query()
            ->whereNotNull('parent_id')
            ->get()
            ->tap(fn(Collection $comments) => $this->getOutput()->progressStart($comments->count()))
            ->each(function (Comment $comment): void {
                dispatch_now(new DeliverCommentReplyNotifications($comment));
                $this->getOutput()->progressAdvance();
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }
}
