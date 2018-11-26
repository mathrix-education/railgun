<?php
namespace App\Controllers;

use App\Models\User;
use App\Responses\PaginationJsonResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Class NotificationController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.3
 * @copyright Mathrix Education SA
 */
class NotificationController extends BaseController
{
    /**
     * Get all notifications of a user.
     *
     * @param Request  $request The request object.
     * @param int|null $userId  The userId, if null, is the current user id.
     * @param int      $page    The page used for pagination.
     * @param int      $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function all(Request $request, int $userId = null, $page = 1, $perPage = 100)
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $userId ?? $user->id;
        $user->canOrFail(DatabaseNotification::class, ["someone", $userId]);
        $target = $userId === $user->id ? $user : User::findOrFail($userId);

        $query = $target->notifications()
            ->orderByDesc("created_at");
        $notifications = $query->limit($perPage)->offset(($page - 1) * $perPage);

        $response = new PaginationJsonResponse(
            $notifications,
            $page,
            $perPage,
            $query->count()
        );

        return $response;
    }

    /**
     * Get unread notifications of a user.
     *
     * @param Request  $request The request object.
     * @param int|null $userId  The userId, if null, is the current user id.
     * @param int      $page    The page used for pagination.
     * @param int      $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function unread(Request $request, int $userId = null, $page = 1, $perPage = 100)
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $userId ?? $user->id;
        $user->canOrFail(DatabaseNotification::class, ["someone", $userId]);
        $target = $userId === $user->id ? $user : User::findOrFail($userId);

        $query = $target->unreadNotifications()->orderByDesc("created_at");

        $notifications = $query->limit($perPage)->offset(($page - 1) * $perPage);

        $response = new PaginationJsonResponse(
            $notifications,
            $page,
            $perPage,
            $query->count()
        );

        return $response;
    }

    /**
     * Get read notifications of a user.
     *
     * @param Request  $request The request object.
     * @param int|null $userId  The userId, if null, is the current user id.
     * @param int      $page    The page used for pagination.
     * @param int      $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function read(Request $request, int $userId = null, $page = 1, $perPage = 100)
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $userId ?? $user->id;
        $user->canOrFail(DatabaseNotification::class, ["someone", $userId]);
        $target = $userId === $user->id ? $user : User::findOrFail($userId);

        $query = $target->readNotifications()->orderByDesc("created_at");

        $notifications = $query->limit($perPage)->offset(($page - 1) * $perPage);

        $response = new PaginationJsonResponse(
            $notifications,
            $page,
            $perPage,
            $query->count()
        );

        return $response;
    }

    /**
     * Mark all notification as read for a user.
     *
     * @param Request  $request The request object.
     * @param int|null $userId  The userId, if null, is the current user id.
     *
     * @return PaginationJsonResponse
     */
    public function markAllRead(Request $request, int $userId = null)
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $userId ?? $user->id;
        $user->canOrFail(DatabaseNotification::class, ["someone", $userId]);
        $target = $userId === $user->id ? $user : User::findOrFail($userId);

        $target->unreadNotifications()->update(["read_at" => Carbon::now()]);

        return $this->all($request, $userId);
    }

    /**
     * Mark a notification as read or unread.
     *
     * @param Request $request        The request object.
     * @param string  $notificationId The notification id (uuid format).
     * @param string  $type           The type of mark (read or unread).
     *
     * @return JsonResponse
     */
    public function mark(Request $request, string $notificationId, string $type)
    {
        /** @var User $user */
        $user = $request->user();
        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::find($notificationId);

        $user->canOrFail("mark", $notification);

        if ($type == "read") {
            $notification->markAsRead();
        } elseif ($type == "unread") {
            $notification->markAsUnread();
        }

        $response = new JsonResponse($notification);

        return $response;
    }
}
