<?php
namespace Tests\Quotes;

/**
 * Class CountriesAsAdminTest
 * Test quotes as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Quotes
 */
class QuotesAsAdminTest extends QuotesAsModeratorTest
{
    /**
     * POST /quotes
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /quotes/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /quotes/{id}
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}