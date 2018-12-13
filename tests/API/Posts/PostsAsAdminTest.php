<?php

namespace Tests\API\Posts;

/**
 * Class PostsAsAdminTest
 * Test Posts as admin.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class PostsAsAdminTest extends PostsAsModeratorTest
{
    /**
     * POST /posts.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess([], function ($data) {
            unset($data["user_id"], $data["created_at"], $data["updated_at"]);

            return $data;
        });
    }

    /**
     * PATCH /posts/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess([], function ($data) {
            unset($data["user_id"], $data["created_at"], $data["updated_at"]);

            return $data;
        });
    }

    /**
     * DELETE /posts/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
