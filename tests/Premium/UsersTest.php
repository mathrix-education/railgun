<?php
namespace Tests\Premium;

use Tests\Premium\Traits\UsersAsc;
use Tests\Premium\Traits\UsersDesc;

/**
 * Class UsersTest
 * Test users as premium
 *
 * @package Tests\Premium
 */
class UsersTest extends PremiumTestCase
{
    use UsersAsc, UsersDesc;
}