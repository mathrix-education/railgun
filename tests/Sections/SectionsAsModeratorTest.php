<?php

namespace Tests\Sections;

/**
 * Class SectionAsModeratorTest
 * Test section as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Section
 */
class SectionsAsModeratorTest extends SectionsAsRegisteredTest
{
    /**
     * GET /section
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * POST /section
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /section/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /section/{id}
     */
    public function testDelete()
    {
        $this->genericDelete();
    }
}