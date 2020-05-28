<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Comment;
use Railgun\Notifications\CommentReplyNotification;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox Mutation dismissNotification
 *
 * Database transactions cause a deadlock, thus there are not used here.
 */
class DismissNotificationIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    mutation ($id: ID!) {
        dismissNotification(id: $id) {
            id
            read_at
        }
    }
    GRAPHQL;

    /**
     * @testdox dismisses a single notification
     */
    public function testDismissNotification(): void
    {
        $user = $this->withPermissions();
        $user->notifyNow(new CommentReplyNotification(Comment::random(), Comment::random()));

        $this->assertCount(1, $user->notifications);
        $notification = $user->notifications->first();

        $this->graphQL(self::QUERY, ['id' => $notification->id]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($notification->id, $this->getData('dismissNotification.id'));
        $this->assertNotNull($this->getData('dismissNotification.read_at'));
        $this->assertCount(0, $user->unreadNotifications);
    }
}
