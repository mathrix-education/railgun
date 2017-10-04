<?php
namespace Tests\Public_;

use Tests\Public_\Traits\UsersAsc;

/**
 * Class UsersTest
 * Test users as public
 *
 * @author Mathrix Team
 * @package Tests\Public_
 */
class UsersTest extends PublicTestCase
{
    use UsersAsc;

    /**
     * Test GET /users/me with 401 unauthorized
     */
    public function testMeUnauthorized()
    {
        $this->call('get', '/users/me');
        $this->assertJsonError('unauthorized', 401);
    }
}