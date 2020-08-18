<?php

declare(strict_types=1);

namespace Tests\E2E\Exceptions;

use Tests\E2E\HttpIntegrationTestCase;

class MethodNotAllowedExceptionIntegrationTest extends HttpIntegrationTestCase
{
    public function testMethodNotAllowed(): void
    {
        $route = $this->faker->uuid;
        $this->app->router->post($route, static fn() => null);

        $this->get($route);
        $this->assertEquals('METHOD_NOT_ALLOWED', $this->getContent('extensions.code'));
    }
}
