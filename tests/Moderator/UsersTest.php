<?php
namespace Tests\Moderator;

use Tests\Moderator\Traits\UsersAsc;
use Tests\Moderator\Traits\UsersDesc;

class UsersTest extends ModeratorTestCase
{
    use UsersAsc, UsersDesc;
}