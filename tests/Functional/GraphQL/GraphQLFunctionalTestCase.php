<?php

declare(strict_types=1);

namespace Tests\Functional\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Mockery;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Tests\ApplicationTestCase;

class GraphQLFunctionalTestCase extends ApplicationTestCase
{
    /**
     * Execute a GraphQL Query or Mutation
     *
     * @param string              $class       The operation class.
     * @param mixed[]             $args        The operation arguments.
     * @param GraphQLContext|null $context     The operation context.
     * @param ResolveInfo|null    $resolveInfo The operation resolver information.
     *
     * @return mixed
     */
    protected function invoke(
        string $class,
        array $args = [],
        ?GraphQLContext $context = null,
        ?ResolveInfo $resolveInfo = null
    )
    {
        $context     ??= Mockery::mock(GraphQLContext::class);
        $resolveInfo ??= Mockery::mock(ResolveInfo::class);

        $instance = $this->app->make($class);

        return $instance(null, $args, $context, $resolveInfo);
    }
}
