<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Railgun\Billing\Checkout\Cart;

class ValidateCart
{
    /**
     * @param null           $_       The parent value, always null in this case.
     * @param mixed[]        $args    The query arguments (cart).
     * @param GraphQLContext $context The GraphQL context which holds the user.
     *
     * @return mixed[]
     */
    public function __invoke($_, array $args, GraphQLContext $context): array
    {
        return new Cart($args['cart']);
    }
}
