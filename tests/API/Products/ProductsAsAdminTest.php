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
        $this->genericAddSuccess([], function ($data) {
            unset($data["created_at"], $data["updated_at"]);
            return $data;
        });
    }

    public function testEdit()
    {
        $this->genericEditSuccess([], function ($data) {
            unset($data["created_at"], $data["updated_at"]);
            return $data;
        });
    }

    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
