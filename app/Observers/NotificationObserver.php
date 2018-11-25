<?php
namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * Class NotificationObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.3
 * @copyright Mathrix Education SA
 */
class NotificationObserver extends BaseObserver
{
    public function saving(DatabaseNotification $notification)
    {
        if (Str::contains($notification->type, "\\")) {
            $t = explode("\\", $notification->type);
            $newType = array_pop($t);
            $newType = str_replace("Notification", "", $newType);
            $newType = lcfirst($newType);
            $newType = Str::snake($newType);

            $notification->type = $newType;
        }

        return true;
    }
}
