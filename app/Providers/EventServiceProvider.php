<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as LumenEventServiceProvider;
use Railgun\Listeners\ChapterWentInvisible;
use Railgun\Listeners\SendCommentReplyNotifications;
use Railgun\Listeners\UpdateSessions;
use Railgun\Listeners\UserChangedGrade;
use Railgun\Models\Chapter;
use Railgun\Models\Comment;
use Railgun\Models\Interaction;
use Railgun\Models\User;

class EventServiceProvider extends LumenEventServiceProvider
{
    protected $listen = [
        'eloquent.created: ' . Comment::class => [SendCommentReplyNotifications::class],
        'eloquent.saved: ' . Interaction::class => [UpdateSessions::class],
        'eloquent.saved: ' . Chapter::class => [ChapterWentInvisible::class],
        'eloquent.saved: ' . User::class => [UserChangedGrade::class],
    ];
}
