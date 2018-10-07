<?php

namespace Tests\API\Scores;

use App\Models\Score;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\IsType;

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
        $signature = md5($score . $token . $game);

        $this->json("post", "/scores/meteor", [
            "score" => $score,
            "token" => $token,
            "signature" => $signature
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("scores", [
            "score" => $score,
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
            "token" => "token",
            "signature" => "invalid"
        ]);

        $this->assertHttp401Unauthorized();
    }

    /**
     * Test score around
     *
     * GET /scores/{game}/around/{score}/{delta}
     */
    public function testAround()
    {
        $score = Score::random();
        Passport::actingAs($score->user, [$this->user->type]);
        $scoreRank = $score->rank;
        $delta = 5;

        $this->json("get", "/scores/meteor/around/{$score->score}/$delta");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "$.[*].id" => new IsType("integer"),
            "$.[*].game" => "meteor",
            "$.[*].rank" => new Callback(function (int $rank) use ($scoreRank, $delta) {
                return $scoreRank - $delta <= $rank && $rank <= $scoreRank + $delta;
            }),
            "$.[*].user_id" => new IsType("integer"),
            "$.[*].created_at" => new IsType("string"),
            "$.[*].updated_at" => new IsType("string"),
            "$.[*].user.id" => new IsType("integer") // Assert user is present
        ]);
        $this->assertJsonLength($delta * 2 + 1);
    }

    /**
     * Test personal best score
     *
     * GET /scores/meteor/personal-best
     */
    public function testPersonalBest()
    {
        $score = Score::random();
        Passport::actingAs($score->user, [$this->user->type]);

        $this->json("get", "/scores/meteor/personal-best");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "$.id" => new IsType("integer"),
            "$.game" => "meteor",
            "$.rank" => new IsType("integer"),
            "$.user_id" => new IsType("integer"),
            "$.created_at" => new IsType("string"),
            "$.updated_at" => new IsType("string"),
            "$.user.id" => new IsType("integer") // Assert user is present
        ]);
    }
}
