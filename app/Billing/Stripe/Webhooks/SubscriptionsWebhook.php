<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Webhooks;

use Illuminate\Contracts\Bus\Dispatcher;
use Railgun\Jobs\UpsertSubscription;
use Stripe\Event;
use Stripe\Subscription;

/**
 * Handler for the Stripe events:
 * - customer.subscription.created
 * - customer.subscription.updated
 * - customer.subscription.deleted
 *
 * @see Event::CUSTOMER_SUBSCRIPTION_CREATED
 * @see Event::CUSTOMER_SUBSCRIPTION_UPDATED
 * @see Event::CUSTOMER_SUBSCRIPTION_DELETED
 */
class SubscriptionsWebhook
{
    /**
     * @param Dispatcher   $dispatcher The bus dispatcher.
     * @param Event        $event      The Stripe event.
     * @param Subscription $object     The subscription object.
     *
     * @return mixed[]
     */
    public function upsert(Dispatcher $dispatcher, Event $event, Subscription $object): array
    {
        $dispatcher->dispatchNow(new UpsertSubscription($object));

        return [
            'message' => 'Subscription handled',
            'event'   => $event->jsonSerialize(),
        ];
    }
}
