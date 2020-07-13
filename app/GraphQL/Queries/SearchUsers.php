<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\User;

/**
 * Query searchUsers.
 * Search users using a query string.
 */
class SearchUsers
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The query arguments (query).
     *
     * @return Builder<User>
     */
    public function __invoke($_, array $args): Builder
    {
        $query = "%{$args['query']}%";

        return User::query()
            ->orWhere('username', 'LIKE', $query)
            ->orWhere('email', 'LIKE', $query)
            ->orWhere('firstname', 'LIKE', $query)
            ->orWhere('lastname', 'LIKE', $query)
            ->orWhere('permissions', 'LIKE', $query)
            ->orWhere('braintree_id', 'LIKE', $query)
            ->orWhere('stripe_id', 'LIKE', $query);
    }
}
