<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Vote;

/**
 * @testdox Model Vote
 * @coversDefaultClass \Railgun\Models\Vote
 */
class VoteTest extends ModelTestCase
{
    /**
     * @covers ::user
     */
    public function testUser(): void
    {
        $this->assertBelongsTo(Vote::class, 'user');
    }

    /**
     * @covers ::comment
     */
    public function testComment(): void
    {
        $this->assertBelongsTo(Vote::class, 'comment');
    }
}
