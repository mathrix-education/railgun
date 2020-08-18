<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exercise;
use function factory;

/** @coversDefaultClass \Railgun\Models\Exercise
 */
class ExerciseTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::subject
     */
    public function testSubject(): void
    {
        $this->assertBelongsTo('subject');
    }

    /**
     * @covers ::getChapter
     */
    public function testGetChapter(): void
    {
        /** @var Exercise $instance */
        $instance = factory(Exercise::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}
