<?php
namespace Tests\Registered;

use Tests\Registered\Traits\UsersAsc;
use Tests\Registered\Traits\UsersDesc;

class UsersTest extends RegisteredTestCase
{
    use UsersAsc, UsersDesc;


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