<?php

declare(strict_types=1);

namespace Railgun\Billing\Bridges;

use Braintree\Exception\NotFound as BraintreeNotFound;
use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use DateTime;
use JsonException;
use Railgun\Models\Subscription;
use Stripe\Subscription as StripeSubscription;
use function app;
use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function storage_path;
use const JSON_THROW_ON_ERROR;

/**
 * Convert a {@see BraintreeSubscription} to an Eloquent {@see Subscription}.
 *
 * @codeCoverageIgnore Only used in Buster.
 */
class BraintreeSubscriptionBridge
{
    /** @var string[] The existing mappings subscriptionId => customerId. */
    private static array $mappings = [];

    /**
     * @param BraintreeSubscription $braintree The Braintree subscription.
     *
     * @return Subscription The converted Eloquent subscription.
     *
     * @throws BraintreeNotFound
     * @throws JsonException
     */
    public static function toEloquent(BraintreeSubscription $braintree): Subscription
    {
        self::buildMappings();

        $subscription = new Subscription();

        $subscription->id          = $braintree->id;
        $subscription->plan        = $braintree->planId;
        $subscription->customer_id = self::getCustomerId($braintree);
        $subscription->status      = self::convertStatus($braintree);

        $subscription->current_period_start = self::toCarbon($braintree->billingPeriodStartDate);
        $subscription->current_period_end   = self::toCarbon($braintree->billingPeriodEndDate);
        $subscription->created_at           = self::toCarbon($braintree->createdAt);
        $subscription->updated_at           = self::toCarbon($braintree->updatedAt);

        if ($subscription->status === StripeSubscription::STATUS_CANCELED) {
            $subscription->canceled_at = self::toCarbon($braintree->updatedAt);
        } else {
            $subscription->canceled_at = null;
        }

        return $subscription;
    }

    /**
     * Build subscriptionId => clientId mappings from a static file.
     *
     * @throws JsonException
     */
    private static function buildMappings(): void
    {
        if (self::$mappings) {
            return;
        }

        $mappings = storage_path('raws/subscription_customers.json');

        if (!file_exists($mappings)) {
            return;
        }

        self::$mappings = json_decode(file_get_contents($mappings), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param BraintreeSubscription $braintree The Braintree subscription.
     *
     * @return string The subscription customer id.
     *
     * @throws BraintreeNotFound
     */
    private static function getCustomerId(BraintreeSubscription $braintree): string
    {
        if (array_key_exists($braintree->id, self::$mappings)) {
            // If the subscription id already exists in the mappings, simply return the customer id.
            return self::$mappings[$braintree->id];
        }

        /** @var Gateway $gateway */
        $gateway = app()->make(Gateway::class);

        // We need to fetch the customer id using th payment method technique
        return $gateway
            ->paymentMethod()
            ->find($braintree->paymentMethodToken)
            ->customerId;
    }

    /**
     * Convert the status of a {@see BraintreeSubscription} to a {@see StripeSubscription}.
     *
     * @param BraintreeSubscription $braintree The Braintree subscription.
     *
     * @return string The new status from {@see StripeSubscription}.
     */
    private static function convertStatus(BraintreeSubscription $braintree): string
    {
        switch ($braintree->status) {
            case BraintreeSubscription::ACTIVE:
                if ($braintree->trialPeriod) {
                    return StripeSubscription::STATUS_TRIALING;
                }

                return StripeSubscription::STATUS_ACTIVE;
            case BraintreeSubscription::PAST_DUE:
                return StripeSubscription::STATUS_PAST_DUE;
            case BraintreeSubscription::CANCELED:
            case BraintreeSubscription::EXPIRED:
                return StripeSubscription::STATUS_CANCELED;
            default:
                return $braintree->status;
        }
    }

    private static function toCarbon(?DateTime $dateTime): ?Carbon
    {
        if ($dateTime === null) {
            return null;
        }

        return Carbon::createFromTimestamp($dateTime->getTimestamp());
    }
}
