<?php

declare(strict_types=1);

namespace Railgun\Billing;

use Braintree\Exception\NotFound as BraintreeNotFound;
use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use DateTime;
use Railgun\Models\Subscription;
use Stripe\Subscription as StripeSubscription;
use const JSON_THROW_ON_ERROR;
use function app;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function storage_path;

class BraintreeSubscriptionBridge
{
    private static array $mappings = [];

    /**
     * @param BraintreeSubscription $braintree The Braintree subscription
     *
     * @return Subscription
     *
     * @throws BraintreeNotFound
     */
    public static function toEloquent(BraintreeSubscription $braintree): Subscription
    {
        self::buildMappings();

        $subscription = new Subscription();

        $subscription->id          = $braintree->id;
        $subscription->plan        = $braintree->planId;
        $subscription->customer_id = self::getCustomerId($braintree);
        $subscription->status      = self::convertStatus($braintree);

        $subscription->payment_intent_secret = null;

        $subscription->current_period_start = self::toCarbon($braintree->billingPeriodStartDate);
        $subscription->current_period_end   = self::toCarbon($braintree->billingPeriodEndDate);
        $subscription->created_at           = self::toCarbon($braintree->createdAt);
        $subscription->updated_at           = self::toCarbon($braintree->updatedAt);

        if ($subscription->status === StripeSubscription::STATUS_CANCELED) {
            $subscription->cancelled_at = self::toCarbon($braintree->updatedAt);
        } else {
            $subscription->cancelled_at = null;
        }

        return $subscription;
    }

    private static function buildMappings(): void
    {
        if (!empty(self::$mappings)) {
            return;
        }

        $mappings = storage_path('raws/subscription_customers.json');

        if (!file_exists($mappings)) {
            return;
        }

        self::$mappings = json_decode(file_get_contents($mappings), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param BraintreeSubscription $braintree
     *
     * @return string
     *
     * @throws BraintreeNotFound
     */
    private static function getCustomerId(BraintreeSubscription $braintree): string
    {
        if (isset(self::$mappings[$braintree->id])) {
            return self::$mappings[$braintree->id];
        }

        /** @var Gateway $gateway */
        $gateway = app()->make(Gateway::class);

        return $gateway
            ->paymentMethod()
            ->find($braintree->paymentMethodToken)
            ->customerId;
    }

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
