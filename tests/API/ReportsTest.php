<?php

declare(strict_types=1);

namespace Tests\API;

use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class ReportsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ReportsTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        'std:index',
        'std:get',
        'std:patch',
        'std:delete',
    ];

    public function testPost()
    {
        $this->makeRESTJsonRequest('std:post', null, ['status' => 'todo']);
    }
}
