<?php

namespace Tests\API_old\Chapters;

use App\Models\Chapter;
use PHPUnit\Framework\Constraint\IsType;
use Tests\API_old\ScopeTestCase;

/**
 * Class ChaptersAsPublicTest.
 * Test chapters as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Chapters
 */
class ChaptersAsPublicTest extends ScopeTestCase
{
    /**
     * GET /chapters -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /chapters/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
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
            "activity_completed" => null,
            "activity_to_revise" => null
        ]);
    }


    /**
     * POST /chapters -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /chapters/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /chapters/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /chapters/by-class-discipline/{class_id}/{discipline_id}.
     */
    public function testByClassAndDiscipline()
    {
        // Ensure that a chapter exists with the given combination of class and discipline
        /** @var Chapter $chapter */
        $chapter = Chapter::random();
        $class = $chapter->class_;
        $discipline = $chapter->discipline;

        $this->json("GET", "/chapters/by-class-discipline/{$class->id}/{$discipline->id}");

        $this->assertHttp200Success();
        $this->assertJsonLengthMin(1);
    }


    /**
     * GET /chapters/recursive/{id}.
     *
     * @throws \Exception
     */
    public function testRecursive()
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::random();
        $this->json("GET", "/chapters/recursive/{$chapter->id}");
        $this->assertHttp200Success();
        $response = $this->getResponseData();

        $notionOrder = [];
        foreach ($response->notions as $notion) {
            $notionOrder[] = $notion->pivot->order;
            foreach ($notion->activities as $activity) {
                $this->assertTrue($activity->visible);
                foreach ($activity->modules as $module) {
                    $this->assertTrue($module->visible);
                }
            }
        }

        $originalNotionOrder = $notionOrder;
        sort($notionOrder);

        $this->assertEquals($notionOrder, $originalNotionOrder);
    }
}
