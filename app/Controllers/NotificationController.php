<?php
namespace App\Controllers;

use App\Models\User;
use App\Responses\PaginationJsonResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Class NotificationController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 */
class NotificationController extends BaseController
{
    /**
     * Get the user notifications with a given relation
     *
     * @param string  $relation
     * @param Request $request
     * @param int     $page
     * @param int     $perPage
     *
     * @return PaginationJsonResponse
     */
    private function getNotificationsJsonResponse(string $relation, Request $request, $page, $perPage)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Builder $query */
        $query = $user->{$relation}();
        $query = $query->orderByDesc("created_at");
        $total = $query->count();

        $notifications = $query->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        $response = new PaginationJsonResponse(
            $notifications,
            $page,
            $perPage,
            $total
        );

        return $response;
    }

    /**
     * Get all notifications of a user.
     *
     * @param Request $request The request object.
     * @param int     $page    The page used for pagination.
     * @param int     $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function all(Request $request, $page = 1, $perPage = 100)
    {
        return $this->getNotificationsJsonResponse("notifications", $request, $page, $perPage);
    }

    /**
     * Get unread notifications of a user.
     *
     * @param Request $request The request object.
     * @param int     $page    The page used for pagination.
     * @param int     $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function unread(Request $request, $page = 1, $perPage = 100)
    {
        return $this->getNotificationsJsonResponse("unreadNotifications", $request, $page, $perPage);
    }

    /**
     * Get read notifications of a user.
     *
     * @param Request $request The request object.
     * @param int     $page    The page used for pagination.
     * @param int     $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function read(Request $request, $page = 1, $perPage = 100)
    {
        return $this->getNotificationsJsonResponse("readNotifications", $request, $page, $perPage);
    }

    /**
     * Mark all notification as read for a user.
     *
     * @param Request $request The request object.
     *
     * @return PaginationJsonResponse
     */
    public function markAllRead(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->unreadNotifications()->update(["read_at" => Carbon::now()]);

        return $this->all($request);
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
