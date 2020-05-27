<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Carbon\Carbon;
use Railgun\Models\Comment;
use Railgun\Notifications\CommentReplyNotification;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function collect;
use function range;

/**
 * @testdox Mutation dismissNotification
 */
class DismissAllNotificationIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    mutation {
        dismissAllNotifications(first: 100) {
            data {
                id
                read_at
            }
        }
    }
    GRAPHQL;

    /**
     * @testdox dismisses all notifications
     */
    public function testDismissAllNotifications(): void
    {
        $notificationCount      = 5;
        $readNotificationsCount = 2;

        // Setup the existing environment
        $user = $this->withPermissions();

        collect(range(1, $notificationCount))->each(static function () use ($user): void {
            $user->notifyNow(new CommentReplyNotification(Comment::random(), Comment::random()));
        });
        $user->notifications()->limit($readNotificationsCount)->update(['read_at' => Carbon::now()]);

        $this->assertCount($notificationCount, $user->notifications);
        $this->assertCount($readNotificationsCount, $user->readNotifications);
        $this->assertCount($notificationCount - $readNotificationsCount, $user->unreadNotifications);

        $this->graphQL(self::QUERY);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $notifications = collect($this->getData('dismissAllNotifications.data'));

        $this->assertCount($notificationCount, $notifications);
        $notifications->each(fn($n) => $this->assertNotNull($n->read_at));

        $user->refresh();

        $this->assertCount($notificationCount, $user->notifications);
        $this->assertCount($notificationCount, $user->readNotifications);
        $this->assertCount(0, $user->unreadNotifications);
    }
}
