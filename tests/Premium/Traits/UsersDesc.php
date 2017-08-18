<?php
namespace Tests\Premium\Traits;

use Tests\Moderator\Traits\UsersDesc as ParentUsersDesc;

trait UsersDesc
{
    use ParentUsersDesc;

    /*
    |--------------------------------------------------------------------------
    | GET /users/{id} stranger
    |--------------------------------------------------------------------------
    */
    /**
     * Test GET /users/{id} for stranger premium
     */
    public function assertGetStrangerPremiumUnauthorized()
    {
        $this->assertCannotGet('premium');
    }

    /**
     * Test GET /users/{id} for stranger registered
     */
    public function assertGetStrangerRegisteredUnauthorized()
    {
        $this->assertCannotGet('registered');
    }
}