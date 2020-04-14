<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Comment;
use Railgun\Models\User;
use function factory;

/**
 * @testdox Model Comment
 * @coversDefaultClass \Railgun\Models\Comment
 */
class CommentTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::parent
     */
    public function testParent(): void
    {
        $this->assertBelongsTo(Comment::class, 'parent');
    }

    /**
     * @covers ::children
     */
    public function testChildren(): void
    {
        $this->assertHasMany(Comment::class, 'children');
    }

    /**
     * @covers ::user
     */
    public function testUser(): void
    {
        $this->assertBelongsTo(Comment::class, 'user');
    }

    /**
     * @covers ::vote
     */
    public function testVote(): void
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $this->assertHasOne(Comment::class, 'vote', [], ['user_id' => $user->getKey()]);
    }

    /**
     * @covers ::votes
     */
    public function testVotes(): void
    {
        $this->assertHasMany(Comment::class, 'votes');
    }
}
