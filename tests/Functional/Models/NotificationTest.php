<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Comment;
use Railgun\Models\Notification;
use function factory;
use function serialize;

/**
 * @coversDefaultClass \Railgun\Models\Notification
 */
class NotificationTest extends ModelTestCase
{
    /**
     * @covers ::getDataAttribute
     */
    public function testGetDataAttribute(): void
    {
        $notification = new Notification();
        $notification->setAttribute('data', [
            'comment' => serialize(factory(Comment::class)->make()),
        ]);

        $this->assertInstanceOf(Comment::class, $notification->data['comment']);
    }
}
