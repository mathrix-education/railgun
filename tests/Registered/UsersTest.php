<?php
namespace Tests\Registered;

use Tests\Registered\Traits\UsersAsc;

class UsersTest extends RegisteredTestCase
{
    use UsersAsc;

    /**
     * Test GET /users/me with 401 unauthorized
     */
    public function testMeSuccess()
    {
        $this->call('get', '/users/me');

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }
}