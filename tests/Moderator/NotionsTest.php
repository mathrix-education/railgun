<?php
namespace Tests\Moderator;

use Mathrix\Lumen\Tests\BaseTestCase;
use Tests\Moderator\Traits\NotionsAsc;
use Tests\Moderator\Traits\NotionsDesc;

/**
 * Class NotionsTest
 * Test notions as moderator
 *
 * @author Mathrix Team
 * @package Tests\Moderator
 */
class NotionsTest extends BaseTestCase
{
    use NotionsAsc, NotionsDesc;
}