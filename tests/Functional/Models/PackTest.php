<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

/**
 * @coversDefaultClass \Railgun\Models\Pack
 */
class PackTest extends ModelTestCase
{
    /**
     * @covers ::trainings
     */
    public function testTrainings(): void
    {
        $this->assertBelongsToMany('trainings', [], [], ['order' => 99]);
    }
}
