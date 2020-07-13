<?php

declare(strict_types=1);

namespace Tests\E2E\Actions;

use Tests\E2E\HttpIntegrationTestCase;

/**
 * @testdox NotFound Action
 */
class NotFoundActionTest extends HttpIntegrationTestCase
{
    /**
     * @testdox returns an HTTP 404 status code
     */
    public function testNotFound(): void
    {
        $this->get('/idontexist');

        $this->assertResponseStatus(404);
    }
}
