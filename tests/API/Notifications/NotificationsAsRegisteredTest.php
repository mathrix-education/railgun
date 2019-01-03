<?php
namespace Tests\API\Notifications;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class NotificationsAsRegisteredTest.
 * Test notifications as registered user.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Notifications
 */
class NotificationsAsRegisteredTest extends NotificationsAsPublicTest
{
    /**
     * Get the notification page signature.
     *
     * @param int|null $page
     * @param int|null $perPage
     *
     * @return array
     */
    protected function getNotificationsPageSignature(int $page = null, int $perPage = null)
    {
        return [
            "page" => $page ?? new IsType("integer"),
            "per_page" => $perPage ?? new IsType("integer"),
            "total" => new IsType("integer"),
            "data.[*].id" => new IsType("string"),
            "data.[*].type" => new IsType("string"),
            "data.[*].notifiable_type" => "user",
            "data.[*].notifiable_id" => new IsType("integer"),
            "data.[*].data" => new IsType("object")
        ];
    }

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

        /** @var DatabaseNotification $notification */
        $notification = $query->firstOrFail();
        $user = $notification->notifiable;
        Passport::actingAs($user, [$this->user->type]);

        $this->json("get", "/notifications/$type/$page/$perPage");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches(
            $this->response->getContent(),
            $this->getNotificationsPageSignature($page, $perPage)
        );
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
        $page = 1;
        $perPage = 100;

        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::inRandomOrder()->firstOrFail();
        $user = $notification->notifiable;
        Passport::actingAs($user, [$this->user->type]);

        $this->json("patch", "/notifications/all/mark/read");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches(
            $this->response->getContent(),
            $this->getNotificationsPageSignature($page, $perPage)
        );
        $this->assertNotInDatabase("notifications", [
            "notifiable_type" => "user",
            "notifiable_id" => $user->id,
            "read_at" => null
        ]);
    }

    /**
     * PATCH /notifications/{notificationId}/mark/read
     */
    public function testMarkRead()
    {
        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::inRandomOrder()->firstOrFail();
        $user = $notification->notifiable;
        Passport::actingAs($user, [$this->user->type]);

        $this->json("patch", "/notifications/{$notification->id}/mark/read");

        $this->assertNotInDatabase("notifications", [
            "id" => $notification->id,
            "notifiable_type" => "user",
            "notifiable_id" => $user->id,
            "read_at" => null
        ]);
    }

    /**
     * PATCH /notifications/{notificationId}/mark/unread
     */
    public function testMarkUnread()
    {
        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::inRandomOrder()->firstOrFail();
        $user = $notification->notifiable;
        Passport::actingAs($user, [$this->user->type]);

        $this->json("patch", "/notifications/{$notification->id}/mark/unread");

        $this->assertInDatabase("notifications", [
            "id" => $notification->id,
            "notifiable_type" => "user",
            "notifiable_id" => $user->id,
            "read_at" => null
        ]);
    }
}
