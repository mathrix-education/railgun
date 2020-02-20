<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Get the login driver public key.
 */
class LoginPublicKey
{
    private Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param null           $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return array
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        return $this->driver->getPublicJWK()->jsonSerialize();
    }
}
