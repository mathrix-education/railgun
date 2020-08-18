<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

/** @coversDefaultClass \Railgun\Models\Vote
 */
class VoteTest extends ModelTestCase
{
    /**
     * @covers ::user
     */
    public function testUser(): void
    {
        $this->assertBelongsTo('user');
    }

    /**
     * @covers ::comment
     */
    public function testComment(): void
    {
        $this->assertBelongsTo('comment');
    }
}
