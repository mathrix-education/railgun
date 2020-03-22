<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Notifications\DatabaseNotification as Notification;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\User;

class DismissNotification
{
    /**
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var User $user */
        $user = $context->user();
        $id   = $args['notificationId'];

        /** @var Notification $notification */
        $notification = $user->notifications()->where('id', '=', $id)->first();

        if (!$notification instanceof Notification) {
            // User does not owns the notification
            return null;
        }

        $notification->markAsRead();

        return $notification;
    }
}
