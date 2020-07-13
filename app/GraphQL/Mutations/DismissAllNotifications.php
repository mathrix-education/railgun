<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification as Notification;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\User;

/**
 * Mutation dismissAllNotifications.
 * Dismiss all the unread notifications of the current user.
 */
class DismissAllNotifications
{
    /**
     * @param null           $_       The parent value, always null in this case.
     * @param mixed[]        $args    The mutation arguments.
     * @param GraphQLContext $context The GraphQL context which holds the user.
     *
     * @return MorphMany<Notification> The notifications builder.
     */
    public function __invoke($_, array $args, GraphQLContext $context): MorphMany
    {
        /** @var User $user */
        $user = $context->user();

        // This is a batch update, we may switch to a more fine-grained solution if events are required.
        Notification::query()
            ->where('notifiable_id', '=', $user->id)
            ->update(['read_at' => Carbon::now()]);

        $user->refresh();

        return $user->notifications();
    }
}
