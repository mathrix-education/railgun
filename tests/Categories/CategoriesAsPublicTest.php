<?php
namespace Tests\Categories;

use Tests\ScopeTestCase;

/**
 * Class CategoriesAsPublicTest
 * Test Categories as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.0
 * @package Tests\Categories
 */
class CategoriesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /categories -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /categories -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /categories/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /categories/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
