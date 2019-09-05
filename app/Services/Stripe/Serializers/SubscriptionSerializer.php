<?php

namespace App\Services\Stripe\Serializers;

use App\Models\User;
use Braintree\CreditCard;
use Braintree\PaymentMethod;
use Braintree\PayPalAccount;
use Braintree\Subscription;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\Transaction\CreditCardDetails;
use Braintree\Transaction\PayPalDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\Card;
use Stripe\Error\InvalidRequest;
use Stripe\Subscription as StripeSubscription;

/**
 * Class SubscriptionSerializer.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class SubscriptionSerializer
{
    /**
     * Serialize a subscription.
     *
     * @param StripeSubscription|BraintreeSubscription $subscription
     *
     * @return array
     */
    public static function serialize($subscription): array
    {
        if ($subscription instanceof StripeSubscription) {
            return SubscriptionSerializer::serializeStripe($subscription);
        } else if ($subscription instanceof BraintreeSubscription) {
            return SubscriptionSerializer::serializeBraintree($subscription);
        } else {
            return $subscription->jsonSerialize();
        }
    }


    /**
     * Serialize a Stripe subscription.
     *
     * @param StripeSubscription $subscription
     *
     * @return array
     */
    public static function serializeStripe(StripeSubscription $subscription): array
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        /** @var Card $card */
        $card = null;

        if ($currentUser->stripe_id === $subscription->customer) {
            /*
             * The current user is the same as the user who owns the subscription. We can try to iterate over its cards
             * to avoid to make an additional request to Stripe.
             */
            $customer = $currentUser->asStripeCustomer();

            /**
             * @var Card $card
             */
            foreach ($customer->sources as $source) {
                if ($source->id === $subscription->default_source) {
                    $card = $source;
                    break;
                }
            }
        }

        if ($card === null) {
            try {
                $card = Card::retrieve($subscription->default_source);
            } catch (InvalidRequest $e) {
                $card = null;
            }
        }

        return [
            "type" => "stripe",
            "id" => $subscription->id,
            "cancel_at" => carbon_auto($subscription->cancel_at),
            "cancel_at_period_end" => $subscription->cancel_at_period_end,
            "canceled_at" => carbon_auto($subscription->canceled_at),
            "created_at" => Carbon::createFromTimestamp($subscription->created),
            "current_period_start" => carbon_auto($subscription->current_period_start),
            "current_period_end" => carbon_auto($subscription->current_period_end),
            "customer" => $subscription->customer,
            "plan" => [
                "id" => $subscription->plan->id,
                "amount" => $subscription->plan->amount,
            ],
            "payment_method" => $card === null ? null : [
                "type" => "card",
                "brand" => Str::slug($card->brand),
                "last4" => $card->last4,
                "expiration" => Carbon::create($card->exp_year, $card->exp_month, 1, 0, 0, 0)
            ],
            "status" => $subscription->status,
            "trial_end" => carbon_auto($subscription->trial_end)
        ];
    }


    /**
     * Serialize a Braintree subscription.
     *
     * @param BraintreeSubscription $subscription
     *
     * @return array
     */
    public static function serializeBraintree(BraintreeSubscription $subscription): array
    {
        $trialEnd = null;

        if ($subscription->trialPeriod) {
            $trialEnd = carbon_auto($subscription->createdAt);

            if ($subscription->trialDurationUnit === "day") {
                $trialEnd->addDays($subscription->trialDuration);
            } else if ($subscription->trialDurationUnit === "month") {
                $trialEnd->addMonths($subscription->trialDuration);
            }
        }

        $cancelAtPeriodEnd = $subscription->currentBillingCycle == $subscription->numberOfBillingCycles;
        $cancelAt = null;

        if ($cancelAtPeriodEnd) {
            $cancelAt = carbon_auto($subscription->nextBillingDate);
        }

        $paymentMethodObj = null;
        $hasTransactions = !empty($subscription->transactions);

        if ($hasTransactions) {
            // If there is at least one transaction, we can retrieve the customer from the latest transaction.
            $customer = $subscription->transactions[0]->customerDetails->id;
        } else {
            // If the is no transaction, we have to find the associated payment method.
            $paymentMethodObj = PaymentMethod::find($subscription->paymentMethodToken);
            $customer = $paymentMethodObj->customerId;
        }

        $paymentMethod = [];

        // Setup payment method
        if ($hasTransactions) {
            // If the transaction has a least one transaction, we can read payment details from the latest one.
            $transaction = $subscription->transactions[0];
            if ($transaction->paymentInstrumentType === "credit_card") {
                /** @var CreditCardDetails $cardDetails */
                $cardDetails = $transaction->creditCardDetails;

                $paymentMethod = [
                    "brand" => Str::slug($cardDetails->cardType),
                    "last4" => $cardDetails->last4,
                    "expiration" => Carbon::create(
                        intval($cardDetails->expirationYear),
                        intval($cardDetails->expirationMonth),
                        1,
                        0,
                        0,
                        0
                    )
                ];
            } else if ($transaction->paymentInstrumentType === "paypal_account") {
                /** @var PayPalDetails $paypalDetails */
                $paypalDetails = $transaction->paypalDetails;

                $paymentMethod = [
                    "brand" => "paypal",
                    "email" => $paypalDetails->payerEmail
                ];
            }
        } else {
            /*
             * If the subscription has no transaction yet, we can read the payment method from the previously retrieved
             * payment method.
             */
            if ($paymentMethodObj instanceof CreditCard) {
                $paymentMethod = [
                    "type" => "card",
                    "brand" => Str::slug($paymentMethodObj->cardType),
                    "last4" => $paymentMethodObj->last4,
                    "expiration" => Carbon::create(
                        intval($paymentMethodObj->expirationYear),
                        intval($paymentMethodObj->expirationMonth),
                        1,
                        0,
                        0,
                        0
                    )
                ];
            } else if ($paymentMethodObj instanceof PayPalAccount) {
                $paymentMethod = [
                    "type" => "paypal",
                    "email" => $paymentMethodObj->email
                ];
            }
        }

        $status = null;

        if ($subscription->trialPeriod) {
            $status = StripeSubscription::STATUS_TRIALING;
        } else {
            // TODO: decide what to do with BraintreeSubscription::PENDING
            switch ($subscription->status) {
                case BraintreeSubscription::ACTIVE:
                    $status = StripeSubscription::STATUS_ACTIVE;
                    break;
                case BraintreeSubscription::CANCELED:
                case BraintreeSubscription::EXPIRED:
                    $status = StripeSubscription::STATUS_CANCELED;
                    break;
                case BraintreeSubscription::PAST_DUE:
                    $status = StripeSubscription::STATUS_PAST_DUE;
                    break;
            }
        }

        return [
            "type" => "braintree",
            "id" => $subscription->id,
            "cancel_at" => $cancelAt,
            "cancel_at_period_end" => $cancelAtPeriodEnd,
            "canceled_at" => in_array($subscription->status, [Subscription::CANCELED, Subscription::EXPIRED])
                ? carbon_auto($subscription->updatedAt) : null,
            "created_at" => carbon_auto($subscription->createdAt),
            "current_period_start" => carbon_auto($subscription->billingPeriodStartDate),
            "current_period_end" => carbon_auto($subscription->billingPeriodEndDate),
            "customer" => $customer,
            "plan" => [
                "id" => $subscription->planId,
                "amount" => round(floatval($subscription->price) * 100)
            ],
            "payment_method" => $paymentMethod,
            "status" => $status,
            "trial_end" => $trialEnd
        ];
    }
}
