<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use Tests\BaseTestCase;

class CORSTest extends BaseTestCase
{
    public function testCors(): void
    {
        $this->options('graphql', [], [
            'Origin'                        => 'graphql.mathrix.fr',
            'Access-Control-Request-Method' => 'POST',
        ]);

        $this->assertResponseStatus(204);
    }
}
