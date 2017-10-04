<?php
namespace Tests\Admin;

use Tests\Moderator\Traits\UsersAsc;

/**
 * Class UsersTest
 * Test users as admin
 *
 * @author Mathrix Team
 * @package Tests\Admin
 */
class UsersTest extends AdminTestCase
{
    use UsersAsc;

    /*
    |--------------------------------------------------------------------------
    | GET /users/{id} for stranger
    |--------------------------------------------------------------------------
    */
    /**
     * Get stranger admin
     * Expect HTTP 200 success
     */
    public function testGetStrangerAdmin()
    {
        $this->assertCanGetStranger('admin');
    }


    /**
     * Get stranger moderator
     * Expect HTTP 200 success
     */
    public function testGetStrangerModerator()
    {
        $this->assertCanGetStranger('moderator');
    }
}