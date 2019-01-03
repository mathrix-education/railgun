<?php
namespace Tests\API\Products;

use Tests\API\ScopeTestCase;

/**
 * Class ProductsAsPublicTest.
 * Test products as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Products
 */
class ProductsAsPublicTest extends ScopeTestCase
{
    /**
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }

    /**
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    /**
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    /**
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
