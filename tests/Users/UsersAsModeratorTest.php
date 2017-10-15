<?php
namespace Tests\Users;

use Tests\ScopeTestCase;

class UsersAsAdminTest extends ScopeTestCase
{
    public function testPointsWithoutLoggable()
    {
        $this->sameAs('registered');
    }


    public function testPointsWithLoggable()
    {
        $this->sameAs('registered');
    }
}