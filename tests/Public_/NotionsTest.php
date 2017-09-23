<?php
namespace Tests\Public_;

use Tests\Public_\Traits\NotionsAsc;
use Tests\Registered\Traits\NotionsDesc;

/**
 * Class NotionsTest
 * Test notions as public
 *
 * @author Mathrix Team
 * @package Tests\Public_
 */
class NotionsTest extends PublicTestCase
{
    use NotionsAsc, NotionsDesc;
}