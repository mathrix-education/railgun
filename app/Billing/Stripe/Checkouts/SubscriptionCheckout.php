<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Checkouts;

use Railgun\Billing\Stripe\Contracts\StripeSellable;
use Railgun\Billing\Stripe\Exceptions\ThreeDSecureException;
use Railgun\Jobs\DispatchCart;
use Railgun\Jobs\UpsertSubscription;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Subscription;
use function sprintf;
use function trans_choice;

class SubscriptionCheckout extends Checkout
{
    protected Subscription $subscription;

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

        $payload = [
            'customer'               => $this->customer->id,
            'items'                  => $this->cart->plans->map(static function (Price $price) {
                return ['price' => $price->id];
            })->toArray(),
            'default_payment_method' => $this->paymentMethodId,
            'add_invoice_items'      => $this->cart->products->map(static function (StripeSellable $product) {
                return [
                    'price'    => $product->stripe_price_id,
                    'quantity' => 1,
                ];
            })->toArray(),
            'expand'                 => ['latest_invoice.payment_intent'],
        ];

        if ($this->cart->getComboCouponId()) {
            $payload['coupon'] = $this->cart->getComboCouponId();
        }

        $this->subscription = $this->client->subscriptions->create($payload);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->invoice = $this->subscription->latest_invoice;

        $this->client->invoices->update($this->invoice->id, [
            'metadata' => $this->cart->toMetadata(),
        ]);

        if ($this->invoice->payment_intent->status === PaymentIntent::STATUS_REQUIRES_ACTION) {
            throw new ThreeDSecureException($this->invoice->payment_intent);
        }

        return $this;
    }

    public function finalize(): self
    {
        if ($this->subscription->status === Subscription::STATUS_ACTIVE) {
            $this->dispatcher->dispatchNow(new UpsertSubscription($this->subscription));
            $this->dispatcher->dispatchNow(new DispatchCart($this->cart, $this->user->id, $this->invoice->id));
        }

        $this->user->flushCache();

        return $this;
    }
}
