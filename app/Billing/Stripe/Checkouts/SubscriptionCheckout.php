<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Checkouts;

use Railgun\Billing\Exceptions\AlreadyPremiumException;
use Railgun\Billing\Stripe\Contracts\StripeSellable;
use Railgun\Jobs\DispatchCart;
use Railgun\Jobs\UpsertSubscription;
use Stripe\Subscription;
use function sprintf;
use function trans_choice;

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
        if ($this->cart->coupon) {
            $this->client->invoiceItems->create([
                'customer'    => $this->customer->id,
                'currency'    => 'eur',
                'amount'      => -$this->cart->couponAmount,
                'description' => sprintf(
                    '%s : "%s"',
                    trans_choice('models.coupon', 1),
                    $this->cart->coupon->id,
                ),
            ]);
        }

        $this->subscription = $this->client->subscriptions->create([
            'customer'               => $this->customer->id,
            'items'                  => [['price' => $this->cart->plan->id]],
            'default_payment_method' => $this->paymentMethodId,
            'add_invoice_items'      => $this->cart->products->map(static function (StripeSellable $product) {
                return [
                    'price'    => $product->stripe_price_id,
                    'quantity' => 1,
                ];
            })->toArray(),
            'expand'                 => ['latest_invoice.payment_intent'],
        ]);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->invoice = $this->subscription->latest_invoice;

        // TODO: check metadata webhook update
        $this->client->invoices->update($this->invoice->id, [
            'metadata' => $this->cart->toMetadata(),
        ]);

        return $this;
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
