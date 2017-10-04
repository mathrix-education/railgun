<?php
namespace Tests\Premium;

use Tests\Premium\Traits\NotionsAsc;
use Tests\Premium\Traits\NotionsDesc;

/**
 * Class NotionsTest
 * Test classes as premium
 *
 * @author Mathrix Team
 * @package Tests\Premium
 */
class NotionsTest extends PremiumTestCase
{
    use NotionsAsc, NotionsDesc;
}