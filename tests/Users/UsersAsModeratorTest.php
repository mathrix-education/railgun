<?php
namespace Tests\Users;

use Tests\ScopeTestCase;

class UsersAsModeratorTest extends ScopeTestCase
{
    public function testPointsWithoutLoggable()
    {
        $this->sameAs('registered');
    }
}