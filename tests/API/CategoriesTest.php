<?php

namespace Tests\API;

use App\Models\Category;
use Mathrix\Lumen\Tests\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\Standard\StandardPostTest;

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
}
