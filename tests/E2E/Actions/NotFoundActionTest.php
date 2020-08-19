<?php

declare(strict_types=1);

namespace Tests\E2E\Actions;

use Tests\E2E\HttpIntegrationTestCase;

class NotFoundActionTest extends HttpIntegrationTestCase
{
    public function testNotFound(): void
    {
        $this->get('/idontexist');

        $this->assertResponseStatus(404);
    }
}
