<?php
namespace Tests\Notions;

/**
 * Class NotionsAsModeratorTest
 * Test notions as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Notions
 */
class NotionsAsModeratorTest extends NotionsAsRegisteredTest
{
    /**
     * POST /notions
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /notions/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /notions/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("activity_notion", ["notion_id" => $this->deleteModelId]);
    }
}
