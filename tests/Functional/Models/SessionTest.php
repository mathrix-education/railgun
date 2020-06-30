<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

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
        $this->assertBelongsTo('user');
    }

    /**
     * @covers ::discipline
     */
    public function testDiscipline(): void
    {
        $this->assertBelongsTo('discipline');
    }

    /**
     * @covers ::chapter
     */
    public function testChapter(): void
    {
        $this->assertBelongsTo('chapter');
    }
}
