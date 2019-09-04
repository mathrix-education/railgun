<?php

namespace Tests\API;

use App\Models\Category;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class CategoriesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class CategoriesTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        "std:index",
        "std:post",
        "std:get",
        "std:patch",
        "rel:get:posts"
    ];


    /**
     * DELETE /categories/{categoryId}
     */
    public function testDelete(): void
    {
        $category = Category::random();
        $fallback = Category::random(["id", "!=", $category->id]);

        $category->posts()->update(["category_id" => $fallback->id]);

        $this->makeRESTJsonRequest("std:delete", null, null, [
            "conditions" => ["id", "=", $category->id]
        ]);
    }
}
