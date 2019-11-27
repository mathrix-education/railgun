<?php

declare(strict_types=1);

namespace Tests\Regression;

use App\Models\Notion;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;

/**
 * @link https://github.com/mathrix-education/education-api/issues/62
 */
class Issue62Test extends APIStatelessTestCase
{
    public function testActivityPostWithReview(): void
    {
        $this->requester = JWT::withScopes('activities:write');

        $this->json('post', '/activities', [
            'name'      => 'test',
            'type'      => 'course',
            'visible'   => true,
            'order'     => 0,
            'premium'   => false,
            'notion_id' => Notion::random()->id,
        ]);

        $this->assertResponseOk();
    }
}
