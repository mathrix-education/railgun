<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

use Illuminate\Support\Collection;
use Railgun\Billing\Stripe\InteractsWithStripe;
use Stripe\Card;
use Stripe\Customer;
use Stripe\ErrorObject;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use function collect;

trait StripeCustomer
{
    use InteractsWithStripe;

    /**
     * @param string|null $paymentMethodId The payment method id.
     *
     * @return Customer The Stripe Customer instance.
     *
     * @throws ApiErrorException
     */
    public function createStripeCustomer(?string &$paymentMethodId = null): Customer
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

        $customer        = $this->client->customers->create($payload);
        $this->stripe_id = $customer->id;
        $this->save();

        if ($paymentMethodId !== null) {
            $paymentMethodId = (string)$customer->invoice_settings->default_payment_method;
        }

        return $customer;
    }

    /**
     * @return Customer The Stripe Customer instance.
     *
     * @throws ApiErrorException
     */
    public function retrieveStripeCustomer(): Customer
    {
        if ($this->stripe_id === null) {
            return $this->createStripeCustomer();
        }

        try {
            return $this->client->customers->retrieve($this->stripe_id);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_MISSING) {
                $this->stripe_id = null;
                $this->save();

                return $this->createStripeCustomer();
            }

            throw $exception;
        }
    }

    /**
     * @param string|null $paymentMethodId The payment method id to apply.
     *
     * @return Customer The Stripe Customer instance.
     *
     * @throws ApiErrorException
     */
    public function updateStripeCustomer(?string &$paymentMethodId = null): Customer
    {
        if (!$this->stripe_id) {
            return $this->createStripeCustomer($paymentMethodId);
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

        try {
            $customer = $this->client->customers->update($this->stripe_id, $payload);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_MISSING) {
                return $this->createStripeCustomer($paymentMethodId);
            }

            throw $exception;
        }

        if ($paymentMethodId !== null) {
            $paymentMethodId = (string)$customer->invoice_settings->default_payment_method;
        }

        return $customer;
    }

    /**
     * @return Collection|PaymentMethod[]
     *
     * @throws ApiErrorException
     */
    public function getStripePaymentMethods(): Collection
    {
        return collect($this->client->paymentMethods->all([
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
    public function attachStripePaymentMethod(string $paymentMethodId): PaymentMethod
    {
        $paymentMethod = $this->client->paymentMethods->retrieve($paymentMethodId);
        if (!isset($paymentMethod->card)) {
            // This is not a card, attach immediately
            $this->client->paymentMethods->attach($paymentMethodId, ['customer' => $this->stripe_id]);
        }

        // This a card, we need to check the
        /** @var Card $card */
        $card           = $paymentMethod->card;
        $paymentMethods = $this->getStripePaymentMethods();

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

        $this->client->paymentMethods->attach($paymentMethodId, ['customer' => $this->stripe_id]);

        return $paymentMethod;
    }
}
