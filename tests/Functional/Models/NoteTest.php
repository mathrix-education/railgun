<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Note;

/**
 * @testdox Model Note
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
        $this->assertBelongsTo(Note::class, 'user');
    }
}
