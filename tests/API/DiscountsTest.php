<?php

declare(strict_types=1);

namespace Tests\API;

use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

class DiscountsTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        'std:index',
        'std:get',
        'std:get:code',
        'std:post',
        'std:patch',
        'std:delete',
        'rel:get:products',
        'rel:patch:products',
    ];
}
