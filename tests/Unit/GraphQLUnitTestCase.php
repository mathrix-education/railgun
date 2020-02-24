<?php

declare(strict_types=1);

namespace Tests\Unit;

use GraphQL\Type\Definition\ResolveInfo;
use Mockery;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Tests\BaseTestCase;

/**
 *
 */
class GraphQLUnitTestCase extends BaseTestCase
{
    /**
     * Execute a GraphQL Query or Mutation
     *
     * @param string              $class
     * @param array               $args
     * @param GraphQLContext|null $context
     * @param ResolveInfo|null    $resolveInfo
     *
     * @return mixed
     */
    protected function invoke(
        string $class,
        array $args = [],
        ?GraphQLContext $context = null,
        ?ResolveInfo $resolveInfo = null
    ) {
        $context     ??= Mockery::mock(GraphQLContext::class);
        $resolveInfo ??= Mockery::mock(ResolveInfo::class);

        $instance = $this->app->make($class);

        return $instance(null, $args, $context, $resolveInfo);
    }
}
