<?php

namespace App\Listeners;

use App\Events\NewSubscriptionEvent;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\TransactionSuccessNotification;
use Illuminate\Support\Facades\Notification;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class NewSubscriptionListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
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
        } else if ($event->initialTransaction !== null) {
            $event->user->notify(new TransactionSuccessNotification(
                $event->user,
                $event->initialTransaction
            ));
        }

        // Send to Google Analytics

    }
}
