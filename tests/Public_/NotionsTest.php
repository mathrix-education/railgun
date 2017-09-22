<?php
namespace Tests\Public_;

use Tests\Public_\Traits\NotionsAsc;
use Tests\Registered\Traits\NotionsDesc;

/**
 * Class NotionsTest
 * Test classes as public
 *
 * @package Tests\Public_
 */
class NotionsTest extends PublicTestCase
{
    use NotionsAsc, NotionsDesc;
}