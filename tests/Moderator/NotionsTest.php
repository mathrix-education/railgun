<?php
namespace Tests\Moderator;

use Mathrix\Lumen\Tests\BaseTestCase;
use Tests\Moderator\Traits\NotionsAsc;
use Tests\Moderator\Traits\NotionsDesc;

class NotionsTest extends BaseTestCase
{
    use NotionsAsc, NotionsDesc;
}