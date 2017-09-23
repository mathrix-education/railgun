<?php
namespace Tests\Moderator\Traits;

use Tests\Premium\Traits\UsersAsc as ParentUsersAsc;

/**
 * Trait UsersAsc
 * Test users as moderator or higher
 *
 * @author Mathrix Team
 * @package Tests\Moderator\Traits
 */
trait UsersAsc
{
    use ParentUsersAsc;


    /**
     * Get stranger premium
     * Expect HTTP 200 success
     */
    public function testGetStrangerPremium()
    {
        $this->assertCanGetStranger('premium');
    }

    /*
    |--------------------------------------------------------------------------
    | GET /users/{id} for strangers
    |--------------------------------------------------------------------------
    */

    /**
     * Assert that user can get a specified user based on its type
     *
     * @param string $type the stranger type to test
     */
    public function assertCanGetStranger(string $type)
    {
        $stranger = $this->getStranger(['type', $type]);

        $this->call('get', '/users/' . $stranger->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }

    /**
     * Get stranger registered
     * Expect HTTP 200 success
     */
    public function testGetStrangerRegistered()
    {
        $this->assertCanGetStranger('registered');
    }
}