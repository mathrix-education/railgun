<?php
namespace App\Listeners;

use App\Events\NewSubscriptionEvent;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\TransactionSuccessNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Class NewSubscriptionListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Listeners
 */
class NewSubscriptionListener extends BaseListener
{
    /**
     * @param NewSubscriptionEvent $event
     */
    public function handle(NewSubscriptionEvent $event)
    {
        Notification::route("slack", env("SLACK_WEBHOOK_PREMIUM"))
            ->notify(new NewSubscriptionNotification($event->user, $event->subscription));

        // Send mail to user
        if ($event->initialTransaction === null && !empty($event->subscription->transactions)) {
            $event->user->notify(new TransactionSuccessNotification(
                $event->user,
                $event->subscription->transactions[0]
            ));
        } elseif ($event->initialTransaction !== null) {
            $event->user->notify(new TransactionSuccessNotification(
                $event->user,
                $event->initialTransaction
            ));
        }

        // Send to Google Analytics

    }
}
