<?php

declare(strict_types=1);

namespace Tests\Functional\Middleware;

use Illuminate\Http\Request;
use Mockery\MockInterface;
use Railgun\Collectors\GraphQLCollector;
use Railgun\Middleware\GraphQLOperationMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Tests\ApplicationTestCase;

/**
 * @testdox GraphQLOperation Middleware
 * @coversDefaultClass \Railgun\Middleware\GraphQLOperationMiddleware
 */
class GraphQLOperationMiddlewareTest extends ApplicationTestCase
{
    /**
     * @testdox does not the Prometheus metrics whe operationName is not provided.
     * @covers ::handle
     * @covers ::terminate
     */
    public function testWithoutOperationName(): void
    {
        $this->mock(GraphQLCollector::class, static function (MockInterface $mock): void {
            $mock->expects('counter->inc')->never();
            $mock->expects('histogram->observe')->never();
        });

        $request = Request::create('/', 'post');

        /** @var GraphQLOperationMiddleware $instance */
        $instance = $this->app->make(GraphQLOperationMiddleware::class);
        $instance->handle($request, static fn() => new Response());
        $instance->terminate($request);
    }

    /**
     * @testdox update the Prometheus metrics whe operationName is provided.
     * @covers ::handle
     * @covers ::terminate
     */
    public function testWithOperationName(): void
    {
        $operationName = $this->faker->uuid;

        $this->mock(GraphQLCollector::class, static function (MockInterface $mock): void {
            $mock->expects('counter->inc');
            $mock->expects('histogram->observe');
        });

        $request = Request::create('/', 'post', ['operationName' => $operationName]);

        /** @var GraphQLOperationMiddleware $instance */
        $instance = $this->app->make(GraphQLOperationMiddleware::class);
        $instance->handle($request, static fn() => new Response());
        $instance->terminate($request);
    }
}
