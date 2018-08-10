<?php
namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

/**
 * Class NewSubscriptionNotification
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Notifications
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class NewSubscriptionNotification extends BaseNotification
{
    /** @var User $user */
    public $user;
    /** @var \Braintree_Subscription $subscription */
    public $subscription;


    /**
     * NewSubscriptionEvent constructor.
     *
     * @param User $user
     * @param \Braintree_Subscription $subscription
     */
    public function __construct(User $user, \Braintree_Subscription $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
    }


    /**
     * @return array
     */
    public function via(): array
    {
        return ["slack"];
    }


    /**
     * Get the Slack representation of the notification.
     *
     * @return SlackMessage
     */
    public function toSlack()
    {
        return (new SlackMessage)
            ->from("Ghost")
            ->to("#code-chat")
            ->content("Nouvel abonnement premium !")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->title("Subscription {$this->subscription->id}")
                    ->fields([
                        "Plan" => $this->subscription->planId,
                        "Email" => $this->user->email
                    ]);
            });
    }
}
