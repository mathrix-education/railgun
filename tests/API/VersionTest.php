<?php

namespace Tests\API;

use PHPUnit\Framework\Constraint\IsType;

/**
 * Class VersionTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.1.0
 * @copyright Mathrix Education SA
 * @package   Tests\API
 */
class VersionTest extends APITestCase
{
    /**
     * GET /.
     */
    public function testVersion()
    {
        $this->json("get", "/");

        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "$.message" => new IsType("string"),
            "$.build" => new IsType("string")
        ]);
    }
}
