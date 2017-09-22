<?php
namespace Tests\Registered;

use Tests\Registered\Traits\NotionsAsc;
use Tests\Registered\Traits\NotionsDesc;

/**
 * Class NotionsTest
 * Test classes as registered
 *
 * @package Tests\Registered
 */
class NotionsTest extends RegisteredTestCase
{
    use NotionsAsc, NotionsDesc;
}