<?php

declare(strict_types=1);

namespace Tests\API;

use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

class ReportsTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        'std:index',
        'std:get',
        'std:patch',
        'std:delete',
    ];

    public function testPost(): void
    {
        $this->makeRESTJsonRequest('std:post', null, ['status' => 'todo']);
    }
}
