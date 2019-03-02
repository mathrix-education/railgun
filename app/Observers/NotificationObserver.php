<?php
namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * Class NotificationObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
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
