<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Support\Collection;
use Railgun\Models\Label;
use function factory;

/**
 * @coversDefaultClass \Railgun\Models\Label
 */
class LabelTest extends ModelTestCase
{
    /**
     * @covers ::courses
     */
    public function testCourses(): void
    {
        /** @var Label $label */
        $label = factory(Label::class)->create();
        $this->assertInstanceOf(Collection::class, $label->courses()->get());
    }

    /**
     * @covers ::exercises
     */
    public function testExercises(): void
    {
        /** @var Label $label */
        $label = factory(Label::class)->create();
        $this->assertInstanceOf(Collection::class, $label->exercises()->get());
    }

    /**
     * @covers ::flashcards
     */
    public function testFlashcards(): void
    {
        /** @var Label $label */
        $label = factory(Label::class)->create();
        $this->assertInstanceOf(Collection::class, $label->flashcards()->get());
    }

    /**
     * @covers ::exams
     */
    public function testExams(): void
    {
        /** @var Label $label */
        $label = factory(Label::class)->create();
        $this->assertInstanceOf(Collection::class, $label->exams()->get());
    }
}
