<?php
namespace Tests\Moderator;

use Mathrix\Lumen\Tests\BaseTestCase;
use Tests\Moderator\Traits\ClassesAsc;
use Tests\Moderator\Traits\ClassesDesc;

class ClassesTest extends BaseTestCase
{
    use ClassesAsc, ClassesDesc;
}