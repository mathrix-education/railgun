<?php
namespace Tests\Public_;

use Tests\Public_\Traits\ClassesAsc;
use Tests\Registered\Traits\ClassesDesc;

/**
 * Class ClassesTest
 * Test classes as public
 *
 * @package Tests\Public_
 */
class ClassesTest extends PublicTestCase
{
    use ClassesAsc, ClassesDesc;
}