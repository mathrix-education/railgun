<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @coversDefaultClass \Railgun\Models\Category
 */
class CategoryTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::posts
     */
    public function testPosts(): void
    {
        $this->assertHasMany('posts');
    }
}
