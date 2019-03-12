<?php

namespace Tests\API\Categories;

use App\Models\Category;

/**
 * Class CategoriesAsAdminTest.
 * Test Categories as admin.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Categories
 */
class CategoriesAsAdminTest extends CategoriesAsModeratorTest
{
    /**
     * POST /categories.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /categories/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /categories/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->deleteModelId = Category::doesntHave("posts")->inRandomOrder()->firstOrFail()->id;
        $this->genericDeleteSuccess();
    }


    /**
     * DELETE /categories/{id}.
     *
     * @throws \Exception
     */
    public function testDeleteWithPosts()
    {
        $this->deleteModelId = Category::has("posts")->inRandomOrder()->firstOrFail()->id;

        $this->genericDelete();

        $this->assertHttp400InvalidRequest();
    }
}
