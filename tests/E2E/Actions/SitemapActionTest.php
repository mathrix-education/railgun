<?php

declare(strict_types=1);

namespace Tests\E2E\Actions;

use Tests\E2E\HttpIntegrationTestCase;

/**
 * @testdox Sitemap Action
 */
class SitemapActionTest extends HttpIntegrationTestCase
{
    /**
     * @testdox returns the sitemap
     */
    public function testSitemap(): void
    {
        $this->get('/sitemap');

        $this->assertResponseOk();
        $this->assertEquals('application/xml', $this->response->headers->get('Content-Type'));
    }
}
