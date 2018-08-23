<?php
namespace Tests\API;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\BaseTestCase;
use Tests\Utils\Traits\Assertions\DatabaseAssertionsTrait;
use Tests\Utils\Traits\Assertions\HttpAssertionsTrait;
use Tests\Utils\Traits\Assertions\JsonAssertionsTrait;
use Tests\Utils\Traits\Assertions\NotificationsAssertionsTrait;
use Tests\Utils\Traits\ResponseTrait;

/**
 * Class APITestCase
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package Tests\API
 * @since
 * @copyright Mathrix Education SA
 */
class APITestCase extends BaseTestCase
{
    use DatabaseAssertionsTrait, HttpAssertionsTrait, JsonAssertionsTrait, NotificationsAssertionsTrait, ResponseTrait,
        DatabaseTransactions;
}
