<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

use Illuminate\Support\Collection;
use Railgun\Models\User;
use Stripe\Card;
use Stripe\Customer;
use Stripe\ErrorObject;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use function collect;
use function iterator_to_array;

/**
 * @property-read Customer                   $stripe_customer
 * @property-read Collection|PaymentMethod[] $stripe_cards
 */
trait StripeCustomer
{
    use InteractsWithStripe;

    /**
     * @throws ApiErrorException
     */
    public static function bootStripeCustomer(): void
    {
        // We only trigger the Stripe synchronization on update since a user is never a Stripe customer by default
        static::updated(static function (User $user): void {
            /**
             * @noinspection NotOptimalIfConditionsInspection That's not true since the retrieving the customer costs
             * an API call.
             */
            if (!$user->stripe_id || !$user->isStripeDirty() || !$user->stripe_customer) {
                return;
            }

            $user->updateStripeCustomer();
        });
    }

    protected function isStripeDirty(): bool
    {
        return $this->stripe_id !== null && $this->isDirty(['email']);
    }

    /**
     * The `stripe_customer` attribute.
     *
     * @return Customer|null The Stripe customer instance if it exists, return null otherwise.
     *
     * @throws ApiErrorException
     */
    public function getStripeCustomerAttribute(): ?Customer
    {
        if (!$this->stripe_id) {
            return null;
        }

        try {
            return $this->client->customers->retrieve($this->stripe_id);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_MISSING) {
                $this->stripe_id = null;
                $this->saveWithoutEvents();

                return null;
            }

            throw $exception; // @codeCoverageIgnore
        }
    }

    /**
     * Ensure that the user is a Stripe customer.
     *
     * @param string|null $paymentMethodId The payment method id to apply.
     *
     * @return Customer The Stripe Customer instance.
     *
     * @throws ApiErrorException
     */
    public function ensureStripeCustomer(?string &$paymentMethodId = null): Customer
    {
        $customer = !$this->stripe_customer
            ? $this->createStripeCustomer($paymentMethodId)
            : $this->updateStripeCustomer($paymentMethodId);

        $this->stripe_id = $customer->id;
        $this->saveWithoutEvents();

        return $customer;
    }

    /**
     * Create a new Stripe customer for the user.
     *
     * @param string|null $paymentMethodId The payment method id.
     *
     * @return Customer The Stripe Customer instance.
     *
     * @throws ApiErrorException
     */
    protected function createStripeCustomer(?string &$paymentMethodId = null): Customer
    {
        // @codeCoverageIgnoreStart
        if ($this->stripe_customer) {
            return $this->updateStripeCustomer($paymentMethodId);
        }
        // @codeCoverageIgnoreEnd

        $payload = [
            'email'             => $this->email,
            'preferred_locales' => ['fr'],
            'metadata'          => [
                'user_id' => $this->id,
            ],
        ];

        if ($paymentMethodId) {
            $payload += [
                'payment_method'   => $paymentMethodId,
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ];
        }

        $customer = $this->client->customers->create($payload);

        if ($paymentMethodId) {
            $paymentMethodId = (string)$customer->invoice_settings->default_payment_method;
        }

        return $customer;
    }

    /**
     * @param string|null $paymentMethodId The payment method id to apply.
     *
     * @return Customer The Stripe Customer instance.
     *
     * @throws ApiErrorException
     */
    protected function updateStripeCustomer(?string &$paymentMethodId = null): Customer
    {
        // @codeCoverageIgnoreStart
        if (!$this->stripe_customer) {
            return $this->createStripeCustomer($paymentMethodId);
        }
        // @codeCoverageIgnoreEnd

        if ($paymentMethodId) {
            $this->attachStripePaymentMethod($paymentMethodId);
        }

        $payload = [
            'email'             => $this->email,
            'preferred_locales' => ['fr'],
            'metadata'          => [
                'user_id' => $this->id,
            ],
        ];

        if ($paymentMethodId !== null) {
            $payload += [
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ];
        }

        $customer = $this->client->customers->update($this->stripe_id, $payload);

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
    public function getStripeCardsAttribute(): Collection
    {
        return collect(iterator_to_array($this->client->paymentMethods->all([
            'customer' => $this->stripe_id,
            'type'     => 'card',
        ])->autoPagingIterator()));
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
    public function attachStripePaymentMethod(string &$paymentMethodId): PaymentMethod
    {
        $paymentMethod = $this->client->paymentMethods->retrieve($paymentMethodId);

        if (!isset($paymentMethod->card)) {
            // This is not a card, attach immediately
            // This code is not used at the moment since we only uses credit cards
            // @codeCoverageIgnoreStart
            $this->client->paymentMethods->attach($paymentMethodId, ['customer' => $this->stripe_id]);
            // @codeCoverageIgnoreEnd
        }

        // This a card, we need to check the fingerprint in order
        /** @var Card $card */
        $card           = $paymentMethod->card;
        $paymentMethods = $this->getStripeCardsAttribute();

        // Compare the embedded card fingerprints to avoid attach the same card twice.
        $paymentMethod = $paymentMethods->first(static function (PaymentMethod $paymentMethod) use ($card) {
            if (!isset($paymentMethod->card)) {
                return false; // @codeCoverageIgnore
            }

            /** @var Card $theCard */
            $theCard = $paymentMethod->card;

            return $theCard->fingerprint === $card->fingerprint;
        });

        if ($paymentMethod === null) {
            // If there is no fingerprint duplicate, attach the new payment method to the user
            $paymentMethod = $this->client->paymentMethods->attach($paymentMethodId, ['customer' => $this->stripe_id]);
        }

        $paymentMethodId = $paymentMethod->id; // Update the payment method reference

        return $paymentMethod;
    }
}
