<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Category;

/**
 * @testdox Model Category
 * @coversDefaultClass \Railgun\Models\Category
 */
class CategoryTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox has a valid relations with posts.
     * @covers ::posts
     */
    public function testPosts(): void
    {
        $this->assertHasMany(Category::class, 'posts');
    }
}
