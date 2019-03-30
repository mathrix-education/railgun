<?php

namespace App\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class NotificationsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.2.0
 */
class NotificationsController extends BaseController
{
    /**
     * Get the user notifications with a given relation
     *
     * @param string $relation unreadNotifications|readNotifications|notifications The relation
     * @param Request $request The Illuminate HTTP request.
     * @param int $page
     * @param int $perPage
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

        $response = new PaginationJsonResponse($query, $page, $perPage);

        return $response;
    }


    /**
     * GET /notifications/unread/{page}/{perPage}
     * Get unread notifications of a user.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $page The page used for pagination.
     * @param int $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function unread(Request $request, $page = 1, $perPage = 100)
    {
        return $this->getNotificationsJsonResponse("unreadNotifications", $request, $page, $perPage);
    }


    /**
     * GET /notifications/read/{page}/{perPage}
     * Get read notifications of a user.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $page The page used for pagination.
     * @param int $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function read(Request $request, $page = 1, $perPage = 100)
    {
        return $this->getNotificationsJsonResponse("readNotifications", $request, $page, $perPage);
    }


    /**
     * GET /notifications/all/{page}/{perPage}
     * Get all notifications of a user.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $page The page used for pagination.
     * @param int $perPage The notification count per page.
     *
     * @return PaginationJsonResponse
     */
    public function all(Request $request, $page = 0, $perPage = 100)
    {
        return $this->getNotificationsJsonResponse("notifications", $request, $page, $perPage);
    }


    /**
     * PATCH /notifications/{notificationId}/mark/{type}
     * Mark a notification as read or unread.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $notificationId The notification id (uuid format).
     * @param string $type The type of mark (read or unread).
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function mark(Request $request, string $notificationId, string $type)
    {
        /** @var User $user */
        $user = $request->user();
        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::query()->find($notificationId);

        $user->canOrFail("mark", $notification);

        if ($type == "read") {
            $notification->markAsRead();
        } else if ($type == "unread") {
            $notification->markAsUnread();
        }

        return new JsonResponse($notification);
    }


    /**
     * PATCH /notifications/all/mark/read
     * Mark all notification as read for a user.
     *
     * @param Request $request The Illuminate HTTP request.
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
}
