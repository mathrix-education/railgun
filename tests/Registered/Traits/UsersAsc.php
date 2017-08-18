<?php
namespace Tests\Registered\Traits;

use Tests\Public_\Traits\UsersAsc as ParentUsersAsc;

trait UsersAsc
{
    use ParentUsersAsc;

    /*
    |--------------------------------------------------------------------------
    | GET /users/{id}
    |--------------------------------------------------------------------------
    */
    public function testGetSuccess()
    {
        $this->call('get', '/users/' . $this->user->id);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }
}