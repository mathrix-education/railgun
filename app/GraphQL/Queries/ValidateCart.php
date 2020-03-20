<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Cart\Cart;
use Railgun\Models\User;

class ValidateCart
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
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var User $user */
        $user = $context->user();

        $cart = new Cart(
            $args['packIds'] ?? [],
            $args['trainingIds'] ?? [],
            $args['premium'] ?? false,
            $args['coupon'] ?? '',
            $user
        );

        return $cart->toGraphQL();
    }
}
