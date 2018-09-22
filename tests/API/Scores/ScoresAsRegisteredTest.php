<?php

namespace Tests\API\Scores;

/**
 * Class ScoresAsPublicTest
 * Test scores as registered user.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.1.0
 */
class ScoresAsRegisteredTest extends ScoresAsPublicTest
{
    /**
     * Test score token retrieving.
     */
    public function testToken()
    {
        $this->json("get", "/scores/meteor/token");

        $this->assertHttp200Success();
        $this->assertJsonKeys(["token"]);
    }

    /**
     * Test score register.
     *
     * POST /scores/{game}
     */
    public function testRegister()
    {
        $score = 1000;
        $time = null;
        $token = "token";
        $game = "meteor";
        $signature = md5($score . "no-time" . $token . $game);

        $this->json("post", "/scores/meteor", [
            "score" => $score,
            "time" => $time,
            "token" => $token,
            "signature" => $signature
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("scores", [
            "score" => $score,
            "time" => null,
            "user_id" => $this->user->id
        ]);
    }

    /**
     * Test score register with an invalid signature.
     *
     * POST /scores/{game}
     */
    public function testInvalidSignature()
    {
        $this->json("post", "/scores/meteor", [
            "score" => 1000,
            "time" => null,
            "token" => "token",
            "signature" => "invalid"
        ]);

        $this->assertHttp401Unauthorized();
    }
}
