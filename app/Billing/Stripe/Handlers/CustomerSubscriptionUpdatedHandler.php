<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Handlers;

use Railgun\Jobs\UpsertSubscription;
use Stripe\Event;
use Stripe\Subscription;
use function dispatch_now;

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
class CustomerSubscriptionUpdatedHandler
{
    /**
     * @param Event        $event  The "customer.subscription.updated" Stripe event.
     * @param Subscription $object The subscription object.
     *
     * @return array<string, mixed>
     */
    public function __invoke(Event $event, Subscription $object): array
    {
        dispatch_now(new UpsertSubscription($object));

        return [
            'message' => 'Subscription handled',
            'event'   => $event->jsonSerialize(),
        ];
    }
}
