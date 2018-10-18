<?php

namespace Tests\API\Scores;

use Tests\API\ScopeTestCase;

/**
 * Class ScoresAsPublicTest
 * Test scores as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 */
class ScoresAsPublicTest extends ScopeTestCase
{
    /**
     * Test top score for a given game.
     *
     * GET /scores/meteor/top/month/20
     */
    public function testTop()
    {
        $this->json("get", "/scores/meteor/top/month/20");

        $this->assertHttp200Success();
        $this->assertJsonLengthMax(20);
    }

    /**
     * Test score register.
     *
     * POST /scores/meteor
     */
    public function testRegister()
    {
        $this->json("post", "/scores/meteor");

        $this->assertHttp401Unauthorized();
    }
}
