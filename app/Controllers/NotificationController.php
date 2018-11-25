<?php
namespace App\Controllers;

use Illuminate\Http\Request;

/**
 * Class NotificationController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.3
 * @copyright Mathrix Education SA
 */
class NotificationController extends BaseController
{
    public function unread(Request $request, int $userId = null)
    {

    }

    public function read(Request $request, int $userId = null)
    {

    }

    public function all(Request $request, int $userId = null)
    {
    }

    public function markAllRead(Request $request, int $userId = null)
    {

    }

    public function mark(Request $request, string $notificationId, string $type)
    {

    }
}
