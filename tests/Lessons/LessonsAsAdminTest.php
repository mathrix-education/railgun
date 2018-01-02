<?php
namespace Tests\Lessons;

/**
 * Class LessonsAsAdminTest
 * Test quotes as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.1
 * @package Tests\Lessons
 */
class LessonsAsAdminTest extends LessonsAsModeratorTest
{
    /**
     * POST /quotes
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /quotes/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /quotes/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
