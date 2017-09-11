<?php
namespace Tests\Registered;

use Tests\Registered\Traits\UsersAsc;
use Tests\Registered\Traits\UsersDesc;

/**
 * Class UsersTest
 * Tests users as registred
 *
 * @package Tests\Registered
 */
class UsersTest extends RegisteredTestCase
{
    use UsersAsc, UsersDesc;
}