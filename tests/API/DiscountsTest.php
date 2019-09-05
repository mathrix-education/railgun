<?php

namespace Tests\API;

use App\Models\Discount;
use App\Models\Product;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardDeleteTest;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class DiscountsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DiscountsTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        "std:index",
        "std:get",
        "std:get:code",
        "std:post",
        "std:patch",
        "std:delete",
        "rel:get:products",
        "rel:patch:products"
    ];
}
