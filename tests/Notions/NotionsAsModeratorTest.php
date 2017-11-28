<?php
namespace Tests\Notions;

/**
 * Class NotionsAsModeratorTest
 * Test notions as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Notions
 */
class NotionsAsModeratorTest extends NotionsAsRegisteredTest
{
    /**
     * POST /notions
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /notions/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /notions/{id}
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("activity_notion", ["notion_id" => $this->deleteModelId]);
    }
}