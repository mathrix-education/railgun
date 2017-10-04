<?php
namespace Tests\Registered;

use Tests\Registered\Traits\NotionsAsc;
use Tests\Registered\Traits\NotionsDesc;

/**
 * Class NotionsTest
 * Test notions as registered
 *
 * @author Mathrix Team
 * @package Tests\Registered
 */
class NotionsTest extends RegisteredTestCase
{
    use NotionsAsc, NotionsDesc;
}