<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Cart\Cart as CartResource;

class Cart
{
    /**
     * @param null           $_       The parent value, always null in this case.
     * @param mixed[]        $args    The query arguments (cart).
     * @param GraphQLContext $context The GraphQL context which holds the user.
     *
     * @return CartResource The validated cart.
     */
    public function __invoke($_, array $args, GraphQLContext $context): CartResource
    {
        return CartResource::build($args)->validate();
    }
}
