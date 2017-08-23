<?php
namespace Tests\Moderator;

use Tests\Moderator\Traits\UsersAsc;
use Tests\Moderator\Traits\UsersDesc;

/**
 * Class UsersTest
 * Test users as moderator
 *
 * @package Tests\Moderator
 */
class UsersTest extends ModeratorTestCase
{
    use UsersAsc, UsersDesc;
}