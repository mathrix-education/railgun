<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Checkouts;

use Railgun\Billing\Exceptions\AlreadyPremiumException;
use Railgun\Jobs\DispatchCart;
use Railgun\Jobs\UpsertSubscription;
use Stripe\Subscription;

class SubscriptionCheckout extends Checkout
{
    protected Subscription $subscription;

    public function validate(): Checkout
    {
        parent::validate();

        if ($this->user->premium) {
            throw new AlreadyPremiumException($this->user->id);
        }

        return $this;
    }

    public function build(): Checkout
    {
        $this->subscription = $this->client->subscriptions->create([
            'customer'               => $this->customer->id,
            'items'                  => [['price' => $this->cart->plan->id]],
            'default_payment_method' => $this->paymentMethodId,
            'expand'                 => ['latest_invoice.payment_intent'],
        ]);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->invoice = $this->subscription->latest_invoice;
    }

    public function finalize(): self
    {
        if ($this->subscription->status === Subscription::STATUS_ACTIVE) {
            $this->dispatcher->dispatchNow(new UpsertSubscription($this->subscription));
            $this->dispatcher->dispatchNow(new DispatchCart($this->cart, $this->user->id, $this->invoice->id));
        }

        return $this;
    }
}
