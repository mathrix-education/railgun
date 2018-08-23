<?php

namespace Tests\API\Products;

/**
 * Class ProductsAsAdminTest
 * Test products as admin.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.1.0
 */
class ProductsAsAdminTest extends ProductsAsModeratorTest
{
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
