<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Webhooks;

use Illuminate\Contracts\Bus\Dispatcher;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\Subscription;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

class SubscriptionsWebhook
{
    /**
     * Handler for the Stripe events:
     * - customer.subscription.created
     * - customer.subscription.updated
     *
     * @see Event::CUSTOMER_SUBSCRIPTION_CREATED
     * @see Event::CUSTOMER_SUBSCRIPTION_UPDATED
     *
     * @param Dispatcher         $dispatcher The bus dispatcher.
     * @param Event              $event      The Stripe event.
     * @param StripeSubscription $object     The subscription object.
     *
     * @return mixed[]
     */
    public function upsert(Dispatcher $dispatcher, Event $event, StripeSubscription $object): array
    {
        $dispatcher->dispatchNow(new UpsertSubscription($object));

        return [
            'message' => 'Subscription handled',
            'event'   => $event->jsonSerialize(),
        ];
    }

    /**
     * Handler for the Stripe events:
     * - customer.subscription.deleted
     *
     * @see Event::CUSTOMER_SUBSCRIPTION_DELETED
     *
     * @param Event              $event  The Stripe event.
     * @param StripeSubscription $object The subscription object.
     *
     * @return mixed[]
     */
    public function delete(Event $event, StripeSubscription $object): array
    {
        $count = Subscription::query()->where('id', '=', $object->id)->delete();

        return [
            'message' => $count === 0
                ? 'Subscription not deleted (not found)'
                : 'Subscription deleted',
            'event'   => $event->jsonSerialize(),
        ];
    }
}
