<?php
namespace Tests\Premium;

use Tests\Premium\Traits\UsersAsc;
use Tests\Premium\Traits\UsersDesc;

class UsersTest extends PremiumTestCase
{
    use UsersAsc, UsersDesc;
}