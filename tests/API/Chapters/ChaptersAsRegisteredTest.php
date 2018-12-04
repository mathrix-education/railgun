<?php

namespace Tests\API\Chapters;

use App\Models\Chapter;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class ChaptersAsPublicTest
 * Test chapters as registered user.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class ChaptersAsRegisteredTest extends ChaptersAsPublicTest
{

    /**
     * GET /chapters/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();

        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "activity_count" => new IsType("integer"),
            "activity_completed" => new IsType("integer"),
            "activity_to_revise" => new IsType("integer")
        ]);
    }

    /**
     * GET /chapters/slug/{chapterSlug}/by-class-slug/{classSlug}.
     *
     * @throws \Exception
     */
    public function testGetBySlugAndClassSlug()
    {
        $chapter = Chapter::random();
        $class = $chapter->class_;

        $this->json("get", "/chapters/slug/{$chapter->slug}/by-class-slug/{$class->slug}");

        $this->assertHttp200Success();
        $this->assertJsonKeys(["id", "name", "slug"]);

        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "activity_count" => new IsType("integer"),
            "activity_completed" => new IsType("integer"),
            "activity_to_revise" => new IsType("integer")
        ]);
    }
}
