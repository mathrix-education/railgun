<?php
namespace Tests\Registered;

use Tests\Registered\Traits\ClassesAsc;
use Tests\Registered\Traits\ClassesDesc;

/**
 * Class ClassesTest
 * Test classes as registered
 *
 * @package Tests\Registered
 */
class ClassesTest extends RegisteredTestCase
{
    use ClassesAsc, ClassesDesc;
}