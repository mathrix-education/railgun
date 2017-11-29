<?php
namespace Tests\Chapters;

/**
 * Class ChaptersAsModeratorTest
 * Test chapters as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Chapters
 */
class ChaptersAsModeratorTest extends ChaptersAsRegisteredTest
{
    /**
     * GET /chapters
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /chapters
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /chapters/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /chapters/{id}
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("chapter_notion", ["chapter_id" => $this->deleteModelId]);
    }
}