<?php

declare(strict_types=1);

namespace Tests\API;

use App\Models\Grade;
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
     * GET /search/{gradeId}/{query}
     */
    public function testContent() : void
    {
        $grade = Grade::random();
        $this->json('get', "/search/{$grade->id}/lorem");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}
