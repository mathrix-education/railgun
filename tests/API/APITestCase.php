<?php
namespace Tests\API;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\BaseTestCase;
use Tests\Utils\Traits\Assertions\DatabaseAssertionsTrait;
use Tests\Utils\Traits\Assertions\HttpAssertionsTrait;
use Tests\Utils\Traits\Assertions\JsonAssertionsTrait;
use Tests\Utils\Traits\Assertions\NotificationsAssertionsTrait;
use Tests\Utils\Traits\DebugTrait;
use Tests\Utils\Traits\ResponseTrait;

/**
 * Class APITestCase.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   Tests\API
 */
class APITestCase extends BaseTestCase
{
    use DatabaseAssertionsTrait, HttpAssertionsTrait, JsonAssertionsTrait, NotificationsAssertionsTrait, ResponseTrait,
        DatabaseTransactions, DebugTrait;
}
