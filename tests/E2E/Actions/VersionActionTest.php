<?php

declare(strict_types=1);

namespace Tests\E2E\Actions;

use Tests\E2E\HttpIntegrationTestCase;

/**
 * @testdox Version Action
 */
class VersionActionTest extends HttpIntegrationTestCase
{
    public function testVersion(): void
    {
        $this->get('/');

        $this->assertResponseOk();
    }
}
