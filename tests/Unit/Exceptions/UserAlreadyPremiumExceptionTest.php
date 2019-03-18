<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\UserAlreadyPremiumException;
use Tests\BaseTestCase;

/**
 * Class UserAlreadyPremiumExceptionTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class UserAlreadyPremiumExceptionTest extends BaseTestCase
{
    public function testConstant()
    {
        $e = new UserAlreadyPremiumException();

        $response = $e->toJsonResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonDocumentMatches($response->getContent(), [
            "error" => "user_already_premium",
            "message" => "User is already premium",
        ]);
        echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);
    }
}
