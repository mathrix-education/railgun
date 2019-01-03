<?php
namespace Tests\API\Votes;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\API\ScopeTestCase;

/**
 * Class VotesAsPublicTest.
 * Test Votes as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @version   4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\API\Votes
 */
class VotesAsPublicTest extends ScopeTestCase
{
    /**
     * POST /votes/{votable_type}/{votable_id}/up.
     */
    public function testUp()
    {
        $comment = Comment::random();

        $this->json("post", "/votes/comment/{$comment->id}/up");

        $this->assertHttp401Unauthorized();
    }

    /**
     * POST /votes/{votable_type}/{votable_id}/down.
     */
    public function testDown()
    {
        $comment = Comment::random();

        $this->json("post", "/votes/comment/{$comment->id}/down");

        $this->assertHttp401Unauthorized();
    }
}
