<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Cart\Cart;
use Railgun\Models\User;

/**
 * Query validateCart.
 * Validate a cart content.
 */
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
        /** @var User $user */
        $user = $context->user();

        $cart = new Cart($args['cart'], $user);

        return $cart->validate()->toGraphQL();
    }
}
