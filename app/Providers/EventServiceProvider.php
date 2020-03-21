<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as LumenEventServiceProvider;
use Railgun\Listeners\SendCommentReplyNotifications;
use Railgun\Models\Comment;

class EventServiceProvider extends LumenEventServiceProvider
{
    protected $listen = [
        'eloquent.created: ' . Comment::class => [SendCommentReplyNotifications::class],
    ];
}
