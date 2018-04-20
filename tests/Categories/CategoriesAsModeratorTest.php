<?php

namespace Tests\Categories;

/**
 * Class CategoriesAsModeratorTest
 * Test Categories as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Categories
 */
class CategoriesAsModeratorTest extends CategoriesAsRegisteredTest
{
    /**
     * POST /categories/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
