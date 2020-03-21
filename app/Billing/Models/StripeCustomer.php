<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Illuminate\Support\Collection;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\Subscription as StripeSubscriptions;
use function collect;

trait StripeCustomer
{
    /**
     * Check if the user is a Stripe Customer.
     *
     * @param bool $strict Check against the Stripe API.
     *
     * @return bool
     */
    public function isStripeCustomer(bool $strict = false): bool
    {
        if ($this->stripe_id === null) {
            return false;
        }

        if (!$strict) {
            return true;
        }

        return $this->asStripeCustomer() instanceof Customer;
    }

    /**
     * Get the user's associated Stripe customer.
     */
    public function asStripeCustomer(): ?Customer
    {
        if (!$this->isStripeCustomer()) {
            return null;
        }

        try {
            return Customer::retrieve($this->stripe_id);
        } catch (ApiErrorException $exception) {
            $this->stripe_id = null;
            $this->save();

            return null;
        }
    }

    /**
     * Create a new Stripe Customer using the user's information.
     *
     * @param string|null $paymentMethodId The default customer source.
     *
     * @return Customer
     *
     * @throws ApiErrorException
     */
    public function createCustomer(?string $paymentMethodId = null): Customer
    {
        $payload = [
            'email'    => $this->email,
            'metadata' => [
                'user_id' => $this->id,
            ],
            'name'     => $this->full_name,
        ];

        if ($paymentMethodId !== null) {
            $payload += [
                'payment_method'   => $paymentMethodId,
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ];
        }

        $customer = Customer::create($payload);

        $this->stripe_id = $customer->id;
        $this->save();

        return $customer;
    }

    /**
     * Get the user's Stripe subscriptions.
     *
     * @return Collection|StripeSubscriptions[]
     *
     * @throws ApiErrorException
     */
    public function getStripeSubscriptions()
    {
        if (!$this->isStripeCustomer()) {
            return new Collection();
        }

        return collect(StripeSubscriptions::all([
            'customer' => $this->stripe_id,
            'expand'   => [
                'data.default_source',
                'data.latest_invoice.payment_intent',
                'data.pending_setup_intent',
            ],
        ]));
    }

    /**
     * @return Collection|PaymentMethod[]
     *
     * @throws ApiErrorException
     */
    public function getPaymentMethods()
    {
        return collect(PaymentMethod::all([
            'customer' => $this->stripe_id,
            'type'     => 'card',
        ]));
    }

    /**
     * @param string $paymentMethodId
     *
     * @return PaymentMethod
     *
     * @throws ApiErrorException
     */
    public function attachPaymentMethod(string $paymentMethodId): PaymentMethod
    {
        $paymentMethod  = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethods = $this->getPaymentMethods();

        // Compare the embedded card fingerprints
        /** @var PaymentMethod $existing */
        $existing = $paymentMethods->first(static function (PaymentMethod $thePaymentMethod) use ($paymentMethod) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            return !empty($thePaymentMethod->card)
                && $paymentMethod->card->fingerprint === $thePaymentMethod->card->fingerprint;
        });

        if ($existing !== null) {
            return $existing;
        }

        $paymentMethod->attach($this->stripe_id);

        return $paymentMethod;
    }
}
