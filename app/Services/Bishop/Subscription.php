<?php

namespace App\Services\Bishop;

use App\Models\User;
use Braintree\CreditCard;
use Braintree\Exception\NotFound;
use Braintree\PayPalAccount;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\Transaction\CreditCardDetails;
use Braintree\Transaction\PayPalDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\Card;
use Stripe\Error\InvalidRequest;
use Stripe\Invoice as StripeInvoice;
use Stripe\SetupIntent as StripeSetupIntent;
use Stripe\Subscription as StripeSubscription;

/**
 * Class Subscription.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 *
 * @property-read string $id
 * @property-read string $type
 * @property-read Carbon $cancel_at
 * @property-read bool $cancel_at_period_end
 * @property-read Carbon $canceled_at
 * @property-read Carbon $created_at
 * @property-read Carbon $current_period_start
 * @property-read Carbon $current_period_end
 * @property-read string $customer
 * @property-read SetupIntent $pending_setup_intent
 * @property-read array $plan
 * @property-read array $payment_method
 * @property-read string $status
 * @property-read Carbon $trial_end
 */
class Subscription extends Bishop
{
    protected $dates = [
        "cancel_at",
        "canceled_at",
        "created_at",
        "current_period_start",
        "current_period_end",
        "payment_method.expiration"
    ];


    /**
     * Serialize a subscription.
     *
     * @param StripeSubscription|BraintreeSubscription $subscription
     *
     * @return self
     */
    public static function from($subscription)
    {
        if ($subscription instanceof StripeSubscription) {
            return new self(self::fromStripe($subscription));
        } else if ($subscription instanceof BraintreeSubscription) {
            return new self(self::fromBraintree($subscription));
        } else {
            return null;
        }
    }


    /**
     * Serialize a Stripe subscription.
     *
     * @param StripeSubscription $subscription
     *
     * @return array
     */
    public static function fromStripe(StripeSubscription $subscription): array
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        /** @var Card $card */
        $card = null;

        if ($subscription->default_source instanceof Card) {
            $card = $subscription->default_source;
        } else if ($currentUser && $currentUser->stripe_id === $subscription->customer) {
            /*
             * The current user is the same as the user who owns the subscription. We can try to iterate over its cards
             * to avoid to make an additional request to Stripe.
             */
            $customer = $currentUser->asStripeCustomer();

            /** @var Card $card */
            foreach ($customer->sources as $source) {
                if ($source->id === $subscription->default_source) {
                    $card = $source;
                    break;
                }
            }

            if ($card === null) {
                // If we did not found the card into the user sources, we can try to retrieve it from the Stripe API.
                $card = $customer->sources->retrieve($subscription->default_source);
            }
        }

        return [
            "type" => "stripe",
            "id" => $subscription->id,
            "cancel_at" => $subscription->cancel_at,
            "cancel_at_period_end" => $subscription->cancel_at_period_end,
            "canceled_at" => $subscription->canceled_at,
            "created_at" => $subscription->created,
            "current_period_start" => $subscription->current_period_start,
            "current_period_end" => $subscription->current_period_end,
            "customer" => $subscription->customer,
            "latest_invoice" => $subscription->latest_invoice instanceof StripeInvoice
                ? Invoice::from($subscription->latest_invoice)
                : $subscription->latest_invoice,
            "pending_setup_intent" => $subscription->pending_setup_intent instanceof StripeSetupIntent
                ? SetupIntent::from($subscription->pending_setup_intent)
                : $subscription->pending_setup_intent,
            "plan" => [
                "id" => $subscription->plan->id,
                "amount" => $subscription->plan->amount,
            ],
            "payment_method" => $card === null ? null : [
                "type" => "card",
                "brand" => Str::slug($card->brand),
                "last4" => $card->last4,
                "expiration" => Carbon::create(
                    $card->exp_year, $card->exp_month, 1, 0, 0, 0
                )->getTimestamp()
            ],
            "status" => $subscription->status,
            "trial_end" => $subscription->trial_end
        ];
    }


    /**
     * Serialize a Braintree subscription.
     *
     * @param BraintreeSubscription $subscription
     *
     * @return array
     */
    public static function fromBraintree(BraintreeSubscription $subscription): array
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
            try {
                $paymentMethodObj = gateway()->paymentMethod()->find($subscription->paymentMethodToken);
                $customer = $paymentMethodObj->customerId;
            } catch (NotFound $exception) {
                $paymentMethodObj = null;
                $customer = null;
            }
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
                    "type" => "card",
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
            } else {
                $paymentMethod = null;
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
            "canceled_at" => in_array($subscription->status, [BraintreeSubscription::CANCELED, BraintreeSubscription::EXPIRED])
                ? $subscription->updatedAt : null,
            "created_at" => $subscription->createdAt,
            "current_period_start" => $subscription->billingPeriodStartDate,
            "current_period_end" => $subscription->billingPeriodEndDate,
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
