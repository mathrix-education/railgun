<?php
namespace Tests\API\Countries;

/**
 * Class CountriesAsAdminTest.
 * Test countries as admin.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @version   1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Countries
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
