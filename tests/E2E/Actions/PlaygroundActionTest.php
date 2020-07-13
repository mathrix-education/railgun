<?php

declare(strict_types=1);

namespace Tests\E2E\Actions;

use Tests\E2E\HttpIntegrationTestCase;

/**
 * @testdox Playground Action
 */
class PlaygroundActionTest extends HttpIntegrationTestCase
{
    public function testPlayground(): void
    {
        $this->get('/playground');

        $this->assertResponseOk();
        $this->assertStringContainsString('GraphQL Playground', $this->response->getContent());
    }
}
