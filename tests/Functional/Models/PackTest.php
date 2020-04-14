<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Pack;

/**
 * @testdox Pack Model
 * @coversDefaultClass \Railgun\Models\Pack
 */
class PackTest extends ModelTestCase
{
    /**
     * @covers ::trainings
     */
    public function testTrainings(): void
    {
        $this->assertBelongsToMany(Pack::class, 'trainings', [], [], ['order' => 99]);
    }
}
