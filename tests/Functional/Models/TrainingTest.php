<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Training;

/**
 * @testdox Model Training
 * @coversDefaultClass \Railgun\Models\Training
 */
class TrainingTest extends ModelTestCase
{
    /**
     * @covers ::packs
     */
    public function testPacks(): void
    {
        $this->assertBelongsToMany(Training::class, 'packs', [], [], ['order' => 99]);
    }

    /**
     * @covers ::sections
     */
    public function testSections(): void
    {
        $this->assertHasMany(Training::class, 'sections');
    }

    /**
     * @covers ::grades
     */
    public function testGrades(): void
    {
        $this->assertBelongsToMany(Training::class, 'grades');
    }
}
