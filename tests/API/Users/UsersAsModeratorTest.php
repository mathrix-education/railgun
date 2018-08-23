<?php

namespace Tests\API\Users;

/**
 * Class UsersAsModeratorTest.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class UsersAsModeratorTest extends UsersAsRegisteredTest
{
    /**
     * POST /users/search.
     */
    public function testSearch()
    {
        $this->call("post", "/users/search", [
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
        $this->call("post", "/users/search", [
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
