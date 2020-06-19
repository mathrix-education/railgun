<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Illuminate\Support\Collection;
use Stripe\Card;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use function collect;
use function stripe;

trait StripeCustomer
{
    /**
     * @param bool $strict If true against the Stripe API, only use the value in the database otherwise.
     *
     * @return bool if the user is a a Stripe customer.
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
     *
     * @return Customer|null if the user is a Stripe customer, its Stripe {@see Customer} representation, null
     *         otherwise.
     */
    public function asStripeCustomer(): ?Customer
    {
        if (!$this->isStripeCustomer()) {
            return null;
        }

        try {
            return stripe()->customers->retrieve($this->stripe_id);
        } catch (ApiErrorException $exception) {
            $this->stripe_id = null;
            $this->save();

            return null;
        }
    }

    /**
     * Create a new Stripe Customer using the user's information.
     *
     * @param string|null $paymentMethodId The default customer payment id, fetched using Stripe.js.
     *
     * @return Customer The newly created Stripe {@see Customer}.
     *
     * @throws ApiErrorException
     */
    public function createCustomer(?string &$paymentMethodId = null): Customer
    {
        if ($this->isStripeCustomer()) {
            $paymentMethodId = $this->attachPaymentMethod($paymentMethodId)->id;

            return $this->asStripeCustomer();
        }

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

        $customer = stripe()->customers->create($payload);

        $this->stripe_id = $customer->id;
        $this->save();

        $paymentMethodId = (string)$customer->invoice_settings->default_payment_method;

        return $customer;
    }

    /**
     * @return Collection|PaymentMethod[]
     *
     * @throws ApiErrorException
     */
    public function getPaymentMethods(): Collection
    {
        return collect(stripe()->paymentMethods->all([
            'customer' => $this->stripe_id,
            'type'     => 'card',
        ])->data);
    }

    /**
     * Attach a payment method id to an existing Stripe {@see Customer}.
     *
     * @param string $paymentMethodId The payment id, fetched using Stripe.js.
     *
     * @return PaymentMethod The Stripe {@see PaymentMethod}.
     *
     * @throws ApiErrorException
     */
    public function attachPaymentMethod(string $paymentMethodId): PaymentMethod
    {
        $paymentMethod = stripe()->paymentMethods->retrieve($paymentMethodId);
        if (!isset($paymentMethod->card)) {
            // This is not a card, attach immediately
            stripe()->paymentMethods->attach($paymentMethodId, ['customer' => $this->stripe_id]);
        }

        // This a card, we need to check the
        /** @var Card $card */
        $card           = $paymentMethod->card;
        $paymentMethods = $this->getPaymentMethods();

        // Compare the embedded card fingerprints to avoid attach the same card twice.
        $existing = $paymentMethods->first(static function (PaymentMethod $paymentMethod) use ($card) {
            if (!isset($paymentMethod->card)) {
                return false;
            }

            /** @var Card $theCard */
            $theCard = $paymentMethod->card;

            return $theCard->fingerprint === $card->fingerprint;
        });

        if ($existing !== null) {
            return $existing;
        }

        stripe()->paymentMethods->attach($paymentMethodId, ['customer' => $this->stripe_id]);

        return $paymentMethod;
    }
}
