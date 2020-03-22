<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Comment;
use Railgun\Notifications\CommentReplyNotification;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox [E2E] read user notifications through me query
 */
class ReadNotificationsIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox successfully read existing notifications
     */
    public function testReadNotifications(): void
    {
        $expectedCount = 5;

        $user = $this->actingAsNewUser();

        for ($i = 0; $i < $expectedCount; $i++) {
            $user->notifyNow(new CommentReplyNotification(Comment::random(), Comment::random()));
        }

        $query = <<<'GRAPHQL'
        query {
            me {
                id
                username
                notifications(first: 10) {
                    data {
                        id
                        type
                        data
                        read_at
                    }
                }
            }
        }
        GRAPHQL;

        $this->graphQL($query);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertCount($expectedCount, $this->getData('me.notifications.data'));
    }
}
