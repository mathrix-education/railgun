<?php

declare(strict_types=1);

namespace Tests\API;

use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class PostsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class PostsTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        'std:index',
        'std:post',
        'std:get',
        'std:patch',
        'std:delete',
    ];

    /**
     * GET /posts/latest
     */
    public function testLatest() : void
    {
        $uri = '/posts/latest';

        $this->json('get', $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}
