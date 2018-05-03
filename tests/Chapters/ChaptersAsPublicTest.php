<?php
namespace Tests\Chapters;

use App\Models\Chapter;
use Tests\ScopeTestCase;

/**
 * Class ChaptersAsPublicTest
 * Test chapters as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Chapters
 */
class ChaptersAsPublicTest extends ScopeTestCase
{
    /**
     * GET /chapters -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /chapters/{id}
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }


    /**
     * POST /chapters -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /chapters/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /chapters/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /chapters/recursive/{id}
     *
     * @throws \Exception
     */
    public function testRecursive()
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::random();
        $this->call("GET", "/chapters/recursive/{$chapter->id}");
        $this->assertHttp200Success();
        $response = $this->getResponseData();

        foreach ($response->notions as $notion) {
            foreach ($notion->activities as $activity) {
                $this->assertTrue($activity->visible);
                foreach ($activity->modules as $module) {
                    $this->assertTrue($module->visible);
                }
            }
        }
    }
}
