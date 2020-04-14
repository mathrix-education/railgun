<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Post;

/**
 * @testdox Model Post
 * @coversDefaultClass \Railgun\Models\Post
 */
class PostTest extends ModelTestCase
{
    /**
     * @covers ::category
     */
    public function testCategory(): void
    {
        $this->assertBelongsTo(Post::class, 'category');
    }
}
