<?php

namespace Tests\API;

use App\Models\Category;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;

/**
 * Class CategoriesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class CategoriesTest extends APITestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest;


    /**
     * DELETE /categories/{categoryId}
     */
    public function testDelete(): void
    {
        $category = Category::random();
        $pivot = Category::random(["id", "!=", $category->id]);

        $category->posts()->update(["category_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $category->id]]);
    }


    /**
     * POST /categories
     * We remove "name" field and assert that we got an error.
     */
    public function testPostHttp422UnprocessableEntity()
    {
        $data = $this->getFactoryBuilder([])->raw();
        unset($data["name"]);

        $this->mockScope("admin");
        $this->json("post", "/categories", $data);

        $this->assertResponseStatus(422);
        $this->assertOpenAPIResponse();
    }
}
