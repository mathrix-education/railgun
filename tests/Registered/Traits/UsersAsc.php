<?php
namespace Tests\Registered\Traits;

use Tests\Public_\Traits\UsersAsc as ParentUsersAsc;

/**
 * Trait UsersAsc
 * Test users as registered or higher
 *
 * @package Tests\Registered\Traits
 */
trait UsersAsc
{
    use ParentUsersAsc;

    /*
    |--------------------------------------------------------------------------
    | GET /users/me
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testMe()
    {
        $this->call('get', '/users/me');

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }


    /*
    |--------------------------------------------------------------------------
    | GET /users/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testGetMe()
    {
        $this->call('get', '/users/' . $this->user->id);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }
}