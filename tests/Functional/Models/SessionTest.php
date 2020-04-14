<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Session;

/**
 * @testdox Model Session
 * @coversDefaultClass \Railgun\Models\Session
 */
class SessionTest extends ModelTestCase
{
    /**
     * @covers ::user
     */
    public function testUser(): void
    {
        $this->assertBelongsTo(Session::class, 'user');
    }

    /**
     * @covers ::discipline
     */
    public function testDiscipline(): void
    {
        $this->assertBelongsTo(Session::class, 'discipline');
    }

    /**
     * @covers ::chapter
     */
    public function testChapter(): void
    {
        $this->assertBelongsTo(Session::class, 'chapter');
    }
}
