<?php

namespace Tests\API\Countries;

/**
 * Class CountriesAsAdminTest
 * Test countries as admin.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 1.0.0
 */
class CountriesAsAdminTest extends CountriesAsModeratorTest
{
    /**
     * POST /countries.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    /**
     * PATCH /countries/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    /**
     * DELETE /countries/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
