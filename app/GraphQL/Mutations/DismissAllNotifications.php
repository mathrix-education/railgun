<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Notifications\DatabaseNotification;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\User;

class DismissAllNotifications
{
    /**
     * Return a value for the field.
     *
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

        // This is a batch update, we may switch to a more fine-grained solution if events are required
        DatabaseNotification::query()
            ->where('notifiable_id', '=', $user->id)
            ->update(['read_at' => Carbon::now()]);

        $user->refresh();

        return $user->notifications();
    }
}
