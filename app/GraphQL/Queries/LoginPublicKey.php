<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use function app;

class LoginPublicKey
{
    /**
     * Return a value for the field.
     *
     * @param null           $rootValue   Usually contains the result returned from the parent field. In this case, it
     *                                    is always `null`.
     * @param mixed[]        $args        The arguments that were passed into the field.
     * @param GraphQLContext $context     Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo    $resolveInfo Information about the query itself, such as the execution state, the field
     *                                    name, path to the field from the root, and more.
     *
     * @return array
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var JWTIssuer $issuer */
        $issuer = app(JWTIssuer::class);

        return $issuer->getJWKPublic()->all();
    }
}
