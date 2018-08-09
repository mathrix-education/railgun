<?php
namespace App\Listeners;

use App\Events\NewSubscriptionEvent;
use App\Notifications\NewSubscriptionNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Class NewSubscriptionListener
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Listeners
 * @since 4.1.0
 * @copyright Mathrix Education SA
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
    }
}
