<?php

namespace Tests\API;

use Tests\APITestCase;

/**
 * Class SearchTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SearchTest extends APITestCase
{
    /**
     * GET /search/{query}
     */
    public function testContent()
    {
        $this->json("get", "/search/13/a/0/100");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}
