<?php

namespace Tests\API\Votes;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\API\ScopeTestCase;

/**
 * Class VotesAsPublicTest
 * Test Votes as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.0.3
 */
class VotesAsPublicTest extends ScopeTestCase
{
    /**
     * POST /votes/{votable_type}/{votable_id}/up.
     */
    public function testUp()
    {
        $votableType = $this->faker->randomElement(Vote::VOTABLE_TYPES);
        /** @var Comment|Post $votableClass */
        $votableClass = Relation::getMorphedModel($votableType);
        /** @var Comment|Post $votable */
        $votable = $votableClass::random();

        $this->call("post", "/votes/$votableType/{$votable->id}/up");

        $this->assertHttp401Unauthorized();
    }

    /**
     * POST /votes/{votable_type}/{votable_id}/down.
     */
    public function testDown()
    {
        $votableType = $this->faker->randomElement(Vote::VOTABLE_TYPES);
        /** @var Comment|Post $votableClass */
        $votableClass = Relation::getMorphedModel($votableType);
        /** @var Comment|Post $votable */
        $votable = $votableClass::random();

        $this->call("post", "/votes/$votableType/{$votable->id}/down");

        $this->assertHttp401Unauthorized();
    }
}
