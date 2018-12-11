<?php

namespace Tests\API\Notifications;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Tests\API\ScopeTestCase;

/**
 * Class NotificationsAsPublicTest
 * Test notifications as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.2.0
 */
class NotificationsAsPublicTest extends ScopeTestCase
{
    /**
     * Generically test notifications.
     *
     * @param string $type
     */
    protected function genericTestNotifications(string $type)
    {
        $page = 1;
        $perPage = 100;

        /** @var Builder $query */
        $query = DatabaseNotification::inRandomOrder();

        switch ($type) {
            case "read":
                $query = $query->whereNotNull("read_at");
                break;
            case "unread":
                $query = $query->whereNull("read_at");
                break;
        }

        $this->json("get", "/notifications/$type/$page/$perPage");

        $this->assertHttp401Unauthorized();
    }

    /**
     * GET /notifications/all
     */
    public function testAll()
    {
        $this->genericTestNotifications("all");
    }

    /**
     * GET /notifications/all
     */
    public function testRead()
    {
        $this->genericTestNotifications("read");
    }

    /**
     * GET /notifications/all
     */
    public function testUnread()
    {
        $this->genericTestNotifications("unread");
    }

    /**
     * GET /notifications/all/mark/read
     */
    public function testMarkAllRead()
    {
        $this->json("patch", "/notifications/all/mark/read");

        $this->assertHttp401Unauthorized();
    }

    /**
     * PATCH /notifications/{notificationId}/mark/read
     */
    public function testMarkRead()
    {
        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::inRandomOrder()->firstOrFail();

        $this->json("patch", "/notifications/{$notification->id}/mark/read");

        $this->assertHttp401Unauthorized();
    }

    /**
     * PATCH /notifications/{notificationId}/mark/unread
     */
    public function testMarkUnread()
    {
        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::inRandomOrder()->firstOrFail();

        $this->json("patch", "/notifications/{$notification->id}/mark/unread");

        $this->assertHttp401Unauthorized();
    }
}
