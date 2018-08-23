<?php

namespace Tests\API\Products;

use Tests\API\ScopeTestCase;

/**
 * Class ProductsAsPublicTest
 * Test products as public (non-logged user).
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 4.1.0
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
