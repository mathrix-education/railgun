<?php
namespace Tests\Premium;

use Tests\Premium\Traits\ClassesAsc;
use Tests\Premium\Traits\ClassesDesc;

/**
 * Class ClassesTest
 * Test classes as premium
 *
 * @author Mathrix Team
 * @package Tests\Premium
 */
class ClassesTest extends PremiumTestCase
{
    use ClassesAsc, ClassesDesc;
}