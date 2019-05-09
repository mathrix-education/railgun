<?php

namespace Tests\API;

use Tests\APIStatelessTestCase;

/**
 * Class SearchTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SearchTest extends APIStatelessTestCase
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
