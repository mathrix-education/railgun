<?php
namespace Tests\Admin;

use Tests\Moderator\Traits\UsersAsc;

class UsersTest extends AdminTestCase
{
    use UsersAsc;

    /*
    |--------------------------------------------------------------------------
    | GET /users/{id} for stranger
    |--------------------------------------------------------------------------
    */
    /**
     * Test GET /users/{id} for premium stranger
     */
    public function testGetStrangerAdmin()
    {
        $this->assertCanGetStranger('admin');
    }


    /**
     * Test GET /users/{id} for registred stranger
     */
    public function testGetStrangerModerator()
    {
        $this->assertCanGetStranger('moderator');
    }
}