<?php
namespace Tests\Moderator\Traits;

use Tests\Premium\Traits\UsersAsc as ParentUsersAsc;

trait UsersAsc
{
    use ParentUsersAsc;


    /*
    |--------------------------------------------------------------------------
    | GET /users/{id} for strangers
    |--------------------------------------------------------------------------
    */
    /**
     * Test GET /users/{id} for premium stranger
     */
    public function testGetStrangerPremium()
    {
        $this->assertCanGetStranger('premium');
    }


    public function assertCanGetStranger(string $type)
    {
        $stranger = $this->getStranger(['type', $type]);

        $this->call('get', '/users/' . $stranger->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }


    /**
     * Test GET /users/{id} for registred stranger
     */
    public function testGetStrangerRegistered()
    {
        $this->assertCanGetStranger('registered');
    }
}