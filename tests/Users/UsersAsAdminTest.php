<?php

namespace Tests\Users;

use Tests\ScopeTestCase;

class UsersAsModeratorTest extends ScopeTestCase
{
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


    public function testLevelObjectivePass()
    {
        $this->sameAs('registered');
    }

}