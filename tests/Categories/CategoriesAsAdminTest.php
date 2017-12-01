<?php
namespace Tests\Categories;

/**
 * Class CategoriesAsAdminTest
 * Test Categories as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Categories
 */
class CategoriesAsAdminTest extends CategoriesAsModeratorTest
{
    /**
     * POST /categories
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /categories/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /categories/{id} -> HTTP 501 not_implemented
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}