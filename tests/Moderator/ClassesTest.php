<?php
namespace Tests\Moderator;

use Mathrix\Lumen\Tests\BaseTestCase;
use Tests\Moderator\Traits\ClassesAsc;
use Tests\Moderator\Traits\ClassesDesc;

/**
 * Class ClassesTest
 * Test classes as moderator
 *
 * @author Mathrix Team
 * @package Tests\Moderator
 */
class ClassesTest extends BaseTestCase
{
    use ClassesAsc, ClassesDesc;
}