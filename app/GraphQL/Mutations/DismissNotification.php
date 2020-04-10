<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Notifications\DatabaseNotification as Notification;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\User;

/**
 * Mutation dismissNotification.
 * Dismiss a single notification.
 */
class DismissNotification
{
    /**
     * @param null           $rootValue The parent value, always null in this case.
     * @param mixed[]        $args      The mutation arguments.
     * @param GraphQLContext $context   The GraphQL context which holds the user.
     *
     * @return Notification|null The dismissed notification.
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context): ?Notification
    {
        /** @var User $user */
        $user = $context->user();

        /** @var Notification $notification */
        $notification = $user->notifications()->where('id', '=', $args['id'])->firstOrFail();
        $notification->markAsRead();

        return $notification;
    }
}
