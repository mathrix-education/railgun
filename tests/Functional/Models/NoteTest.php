<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @coversDefaultClass \Railgun\Models\Note
 */
class NoteTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::user
     */
    public function testUser(): void
    {
        $this->assertBelongsTo('user');
    }
}
