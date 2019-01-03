<?php
namespace Tests\API\Users;

/**
 * Class UsersAsModeratorTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Users
 */
class UsersAsModeratorTest extends UsersAsRegisteredTest
{
    /**
     * POST /users/search.
     */
    public function testSearch()
    {
        $this->json("post", "/users/search", [
            "username" => "%a%",
            "order" => [
                "column" => "created_at",
                "dir" => "desc",
            ],
            "having" => [
                "comments" => [
                    "content" => "%a%",
                ],
            ],
        ]);

        $this->assertHttp200Success();
        $this->assertJsonKeys(["perPage", "page", "count", "data"]);
    }

    /**
     * POST /users/search with wrong order direction.
     */
    public function testSearchWrongOrder()
    {
        $this->json("post", "/users/search", [
            "username" => "%a%",
            "order" => [
                "column" => "created_at",
                "dir" => "wrong",
            ],
            "having" => [
                "comments" => [
                    "content" => "%a%",
                ],
            ],
        ]);

        $this->assertHttp400InvalidRequest();
    }
}
