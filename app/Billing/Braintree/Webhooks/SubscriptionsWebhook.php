<?php

declare(strict_types=1);

namespace Railgun\Billing\Braintree\Webhooks;

use Braintree\Subscription;
use Braintree\WebhookNotification;
use Illuminate\Contracts\Bus\Dispatcher;
use Railgun\Jobs\UpsertSubscription;

class SubscriptionsWebhook
{
    /**
     * Handler for the Braintree events:
     *
     * @see WebhookNotification::SUBSCRIPTION_CANCELED
     * @see WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY
     * @see WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY
     * @see WebhookNotification::SUBSCRIPTION_EXPIRED
     * @see WebhookNotification::SUBSCRIPTION_TRIAL_ENDED
     * @see WebhookNotification::SUBSCRIPTION_WENT_ACTIVE
     * @see WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE
     *
     * @param Dispatcher          $dispatcher   The bus dispatcher.
     * @param WebhookNotification $event        The Braintree event.
     * @param Subscription        $subscription The Subscription object.
     *
     * @return mixed[]
     */
    public function upsert(
        Dispatcher $dispatcher,
        WebhookNotification $event,
        Subscription $subscription
    ): array
    {
        $dispatcher->dispatchNow(new UpsertSubscription($subscription));

        return [
            'message' => 'Subscription handled',
            'event'   => $event->jsonSerialize(),
        ];
    }
}
