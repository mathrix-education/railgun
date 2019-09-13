<?php

declare(strict_types=1);

namespace App\Services\Stripe\Models;

use Illuminate\Support\Collection;
use Stripe\Card as StripeCard;
use Stripe\Customer;
use Stripe\Error\Api;
use Stripe\Subscription;
use Stripe\Token;

/**
 * Trait StripeCustomer.
 *
 * @property string $stripe_id The Stripe customer id. Starts with "cus_".
 */
trait StripeCustomer
{
    public static $StripeCustomerCacheKey      = 'stripe.customer';
    public static $StripeCardsCacheKey         = 'stripe.cards';
    public static $StripeSubscriptionsCacheKey = 'stripe.subscriptions';

    /**
     * Create a new Stripe Customer using the user's information.
     *
     * @param string|null $source The default customer source.
     *
     * @return Customer
     */
    public function createStripeCustomer(?string $source = null) : Customer
    {
        $customer = Customer::create([
            'email' => $this->email,
            'metadata' => [
                'user_id' => $this->id,
            ],
            'name' => $this->full_name,
            'source' => $source,
        ]);

        $this->stripe_id = $customer->id;
        $this->save();

        return $customer;
    }

    /**
     * Get the user's Stripe subscriptions.
     *
     * @return Collection|Subscription[]
     */
    public function getStripeSubscriptions() : Collection
    {
        if (! $this->isStripeCustomer()) {
            return new Collection();
        }

        return $this->cache(StripeCustomer::$StripeSubscriptionsCacheKey, 7 * 24 * 3600, function () {
            $subscriptions = new Collection();

            $stripeCollection = Subscription::all([
                'customer' => $this->stripe_id,
                'expand' => [
                    'data.default_source',
                    'data.latest_invoice.payment_intent',
                    'data.pending_setup_intent',
                ],
            ]);

            foreach ($stripeCollection as $subscription) {
                $subscriptions->push($subscription);
            }

            return $subscriptions;
        });
    }

    /**
     * Check if the user is a Stripe Customer.
     *
     * @param bool $strict Check against the Stripe API.
     *
     * @return bool
     */
    public function isStripeCustomer(bool $strict = false) : bool
    {
        if ($this->stripe_id === null) {
            return false;
        }

        if (! $strict) {
            return true;
        }

        return $this->asStripeCustomer() instanceof Customer;
    }

    /**
     * Get the user's associated Stripe customer.
     */
    public function asStripeCustomer() : ?Customer
    {
        if (! $this->isStripeCustomer()) {
            return null;
        }

        return $this->cache(StripeCustomer::$StripeCustomerCacheKey, 7 * 24 * 3600, function () {
            try {
                return Customer::retrieve($this->stripe_id);
            } catch (Api $exception) {
                $this->stripe_id = null;
                $this->save();

                return null;
            }
        });
    }

    /**
     * Add a source to the user.
     *
     * @param string $cardToken
     * @param bool   $makeDefault
     *
     * @return StripeCard
     */
    public function addSource(string $cardToken, bool $makeDefault = false) : StripeCard
    {
        // Check if card already exists
        $token = Token::retrieve($cardToken);
        /** @var StripeCard $card */
        $card            = null;
        $cards           = $this->getStripeCards();
        $alreadyAttached = false;

        /** @var StripeCard $card */
        foreach ($cards as $card) {
            if ($card->fingerprint === $token->card->fingerprint) {
                // Card already attached
                $alreadyAttached = true;
                // $card            = $token->card;
                break;
            }
        }

        if (! $alreadyAttached) {
            /** @var StripeCard $card */
            $card = Customer::createSource($this->stripe_id, ['source' => $cardToken]);

            $this->invalidate(StripeCustomer::$StripeCardsCacheKey);

            if ($makeDefault) {
                // $card will always be defined
                Customer::update($this->stripe_id, [
                    'default_source' => $card->id,
                ]);
            }
        }

        $this->invalidate(StripeCustomer::$StripeCustomerCacheKey);

        return $card;
    }

    /**
     * Ge the user's Stripe cards.
     */
    public function getStripeCards() : Collection
    {
        return $this->cache(StripeCustomer::$StripeCardsCacheKey, 7 * 24 * 3600, function () {
            $cards = new Collection();

            $stripeCards = Customer::allSources($this->stripe_id, [
                'limit' => 100,
                'object' => 'card',
            ]);

            foreach ($stripeCards as $stripeCard) {
                $cards->push($stripeCard);
            }

            return $cards;
        });
    }
}
