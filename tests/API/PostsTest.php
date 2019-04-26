<?php

namespace Tests\API;

use App\Models\Category;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardDeleteTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

/**
 * Class PostsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class PostsTest extends APITestCase
{
    use StandardIndexTest, StandardGetTest, StandardPostTest, StandardPatchTest, StandardDeleteTest;


    /**
     * GET /posts/by-module/{moduleId}
     */
    public function testByCategory(): void
    {
        $this->assertRestBySuccess(Category::class);
    }


    /**
     * GET /posts/latest/{page}/{perPage}
     */
    public function testLatest(): void
    {
        $uri = "/posts/latest/1/10";

        JWT::autoScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}
