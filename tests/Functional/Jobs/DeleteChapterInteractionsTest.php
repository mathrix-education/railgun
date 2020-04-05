<?php

declare(strict_types=1);

namespace Tests\Functional\Jobs;

use Railgun\Jobs\DeleteChapterInteractions;
use Railgun\Models\Course;
use Tests\ApplicationTestCase;

/**
 * @testdox [Functional] DeleteChapterInteractions Job
 * @coversDefaultClass \Railgun\Jobs\DeleteChapterInteractions
 */
class DeleteChapterInteractionsTest extends ApplicationTestCase
{
    /**
     * @testdox deletes the existing interactions and sessions of a course
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $course = Course::random();

        (new DeleteChapterInteractions($course))->handle();

        $this->notSeeInDatabase('sessions', [
            'chapter_id' => $course->getChapter()->id,
        ]);
        $this->notSeeInDatabase('interactions', [
            'interactable_type' => 'course',
            'interactable_id'   => $course->id,
        ]);
    }
}
