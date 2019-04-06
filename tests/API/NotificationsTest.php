<?php

namespace Tests\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Laravel\Passport\Passport;

/**
 * Class NotificationsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class NotificationsTest extends APITestCase
{
    /**
     * Generically test notifications.
     *
     * @param string $type
     */
    private function genericTestNotifications(string $type)
    {
        $page = 0;
        $perPage = 100;

        /** @var Builder $query */
        $query = DatabaseNotification::query()->inRandomOrder();
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
        /** @var User $user */
        $user = $notification->notifiable;

        Passport::actingAs($user, explode(" ", $user->getPassportScopes()));
        $this->json("get", "/notifications/$type/$page/$perPage");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
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
        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::query()
            ->whereNotNull("read_at")
            ->inRandomOrder()
            ->firstOrFail();
        /** @var User $user */
        $user = $notification->notifiable;
        Passport::actingAs($user, explode(" ", $user->getPassportScopes()));

        $this->json("patch", "/notifications/all/mark/read");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

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
        $notification = DatabaseNotification::query()
            ->inRandomOrder()
            ->firstOrFail();
        /** @var User $user */
        $user = $notification->notifiable;
        Passport::actingAs($user, explode(" ", $user->getPassportScopes()));

        $this->json("patch", "/notifications/{$notification->id}/mark/read");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

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
        $notification = DatabaseNotification::query()
            ->inRandomOrder()
            ->firstOrFail();
        /** @var User $user */
        $user = $notification->notifiable;
        Passport::actingAs($user, explode(" ", $user->getPassportScopes()));

        $this->json("patch", "/notifications/{$notification->id}/mark/unread");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $this->assertInDatabase("notifications", [
            "id" => $notification->id,
            "notifiable_type" => "user",
            "notifiable_id" => $user->id,
            "read_at" => null
        ]);
    }
}
