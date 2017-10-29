<?php

namespace Tests\Users;

use Tests\ScopeTestCase;

class UsersAsModeratorTest extends ScopeTestCase
{
    public function testEdit()
    {
        $this->sameAs('registered');
    }

    public function testPointsWithoutLoggable()
    {
        $this->sameAs('registered');
    }


    public function testPointsWithLoggable()
    {
        $this->sameAs('registered');
    }


    public function testDailyAchievementPass()
    {
        $this->sameAs('registered');
    }


    public function testLevelPass()
    {
        $this->sameAs('registered');
    }
}