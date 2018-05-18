<?php
namespace Tests\Sections;

/**
 * Class SectionAsAdminTest
 * Test section as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Section
 */
class SectionsAsAdminTest extends SectionsAsModeratorTest
{
    /**
     * GET /section
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /section
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /section/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /section/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
    }


    /**
     * GET /trainings/{id}/recursive as buyer
     *
     * @throws \Exception
     */
    public function testRecursiveAsBuyer()
    {
        $this->generic(true, "/sections/{id}/recursive", true);
        $this->assertIsRecursive();
    }


    /**
     * GET /trainings/{id}/recursive as non buyer
     *
     * @throws \Exception
     */
    public function testRecursiveAsNonBuyer()
    {
        $this->generic(false, "/sections/{id}/recursive", true);
        $this->assertIsRecursive();
    }
}
