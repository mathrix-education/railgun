<?php

declare(strict_types=1);

namespace Railgun\Models;

use Braintree\CustomerSearch;
use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\PayPalAccount;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Railgun\Billing\Stripe\Models\InteractsWithStripe;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Models\Contracts\Cacheable as CacheableContract;
use Railgun\Models\Traits\Cacheable;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\Price;
use Stripe\Subscription as StripeSubscription;
use function app;
use function in_array;
use function str_pad;
use const STR_PAD_LEFT;

/**
 * @property      string       $id
 * @property      string       $plan
 * @property      string       $customer_id
 * @property      string       $status
 * @property      Carbon       $current_period_start
 * @property      Carbon       $current_period_end
 * @property      Carbon       $created_at
 * @property      Carbon       $updated_at
 * @property      Carbon|null  $canceled_at
 * ---
 * @property-read string       $processor
 * @property-read Invoice|null $latest_invoice
 * @property-read mixed[]|null $card
 * ---
 * @property-read User         $user
 */
class Subscription extends BaseModel implements CacheableContract
{
    use Cacheable;
    use InteractsWithStripe;

    public const PROCESSOR_STRIPE           = 'stripe';
    public const PROCESSOR_STRIPE_LEGACY    = 'stripe_legacy';
    public const PROCESSOR_BRAINTREE_LEGACY = 'braintree_legacy';
    public const BRAINTREE_STATUS_MAP       = [
        // Subscriptions
        BraintreeSubscription::ACTIVE         => StripeSubscription::STATUS_ACTIVE,
        BraintreeSubscription::CANCELED       => StripeSubscription::STATUS_CANCELED,
        BraintreeSubscription::EXPIRED        => StripeSubscription::STATUS_CANCELED,
        BraintreeSubscription::PAST_DUE       => StripeSubscription::STATUS_PAST_DUE,
        BraintreeSubscription::PENDING        => StripeSubscription::STATUS_ACTIVE,
        // Invoices
        Transaction::AUTHORIZATION_EXPIRED    => Invoice::STATUS_UNCOLLECTIBLE,
        Transaction::AUTHORIZED               => Invoice::STATUS_OPEN,
        Transaction::AUTHORIZING              => Invoice::STATUS_OPEN,
        Transaction::SETTLEMENT_PENDING       => Invoice::STATUS_UNCOLLECTIBLE,
        Transaction::SETTLEMENT_DECLINED      => Invoice::STATUS_UNCOLLECTIBLE,
        Transaction::FAILED                   => Invoice::STATUS_UNCOLLECTIBLE,
        Transaction::GATEWAY_REJECTED         => Invoice::STATUS_UNCOLLECTIBLE,
        Transaction::PROCESSOR_DECLINED       => Invoice::STATUS_UNCOLLECTIBLE,
        Transaction::SETTLED                  => Invoice::STATUS_PAID,
        Transaction::SETTLING                 => Invoice::STATUS_PAID,
        Transaction::SUBMITTED_FOR_SETTLEMENT => Invoice::STATUS_OPEN,
        Transaction::VOIDED                   => Invoice::STATUS_VOID,
    ];

    public    $incrementing = false;
    protected $keyType      = 'string';
    protected $fillable     = [
        'id',
        'plan',
        'customer_id',
        'status',
        'current_period_start',
        'current_period_end',
        'created_at',
        'updated_at',
        'canceled_at',
    ];
    protected $casts        = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'canceled_at'          => 'datetime',
    ];

    public static function fromStripe(StripeSubscription $stripeSubscription): self
    {
        $subscription = new self([
            'id'                   => $stripeSubscription->id,
            'customer_id'          => $stripeSubscription->customer->id ?? $stripeSubscription->customer,
            'status'               => $stripeSubscription->status,
            'current_period_start' => Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end'   => Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            'created_at'           => Carbon::createFromTimestamp($stripeSubscription->created),
            'updated_at'           => Carbon::createFromTimestamp($stripeSubscription->created),
        ]);

        // @codeCoverageIgnoreStart
        /** @var Price $price */
        $price = $stripeSubscription->items->data[0]->price;

        if (in_array($price->lookup_key, ModeMachine::PLANS, true)) {
            $subscription->plan = $price->lookup_key;
        } else {
            $subscription->plan = $price->id;
        }

        $canceledAt = $stripeSubscription->canceled_at ?? $stripeSubscription->cancel_at;
        if ($canceledAt !== null) {
            $subscription->canceled_at = Carbon::createFromTimestamp($canceledAt);
        }

        // @codeCoverageIgnoreEnd

        return $subscription;
    }

    public static function fromBraintree(BraintreeSubscription $btSubscription): Subscription
    {
        $gateway = app()->make(Gateway::class);

        $status = self::BRAINTREE_STATUS_MAP[$btSubscription->status];

        // We need to use the customer search workaround
        $customerResults = $gateway->customer()->search([
            CustomerSearch::paymentMethodToken()->is($btSubscription->paymentMethodToken),
        ]);
        $customerId      = $customerResults->getIds()[0] ?? 'unknown';

        return new self([
            'id'                   => $btSubscription->id,
            'plan'                 => $btSubscription->planId,
            'customer_id'          => $customerId,
            'status'               => $status,
            'current_period_start' => Carbon::instance($btSubscription->billingPeriodStartDate),
            'current_period_end'   => Carbon::instance($btSubscription->billingPeriodEndDate),
            'created_at'           => Carbon::instance($btSubscription->createdAt),
            'updated_at'           => Carbon::instance($btSubscription->updatedAt),
            'canceled_at'          => $status === StripeSubscription::STATUS_CANCELED
                ? Carbon::instance($btSubscription->updatedAt)
                : null,
        ]);
    }

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'stripe_id');
    }

    /**
     * @return string The processor name.
     */
    public function getProcessorAttribute(): string
    {
        if (
            Str::startsWith($this->plan, 'v2')
            || Str::startsWith($this->plan, 'v3')
            || Str::startsWith($this->plan, 'v5')
        ) {
            return self::PROCESSOR_STRIPE_LEGACY;
        }

        if (Str::startsWith($this->plan, 'v4')) {
            return self::PROCESSOR_BRAINTREE_LEGACY;
        }

        return self::PROCESSOR_STRIPE;
    }

    /**
     * Get the Stripe latest_invoice field with the nested payment_intent.
     *
     * @return Invoice|null The Stripe {@see StripeSubscription} latest {@see Invoice}.
     */
    public function getLatestInvoiceAttribute(): ?Invoice
    {
        return $this->getCache()->rememberForever('latest_invoice', function () {
            if ($this->processor === self::PROCESSOR_BRAINTREE_LEGACY) {
                // Braintree, convert to a fake Stripe Invoice
                try {
                    $btSubscription = app()->make(Gateway::class)->subscription()->find($this->id);
                } catch (NotFound $e) {
                    return null;
                }

                if (!$btSubscription || !isset($btSubscription->transactions[0])) {
                    return null; // @codeCoverageIgnore
                }

                $transaction = $btSubscription->transactions[0];

                return Invoice::constructFrom([
                    'object'             => Invoice::OBJECT_NAME,
                    'id'                 => $transaction->id,
                    'amount_paid'        => (int)((float)$transaction->amount) * 100,
                    'hosted_invoice_url' => null,
                    'invoice_pdf'        => null,
                    'payment_intent'     => null,
                    'number'             => $transaction->id,
                    'status'             => self::BRAINTREE_STATUS_MAP[$transaction->status],
                ]);
            }

            // Stripe, hot reload the client
            $localStripeClient = $this->client;

            if ($this->processor === self::PROCESSOR_STRIPE_LEGACY) {
                $localStripeClient = app()->make(StripeClientRegistry::class)->make('secondary');
            }

            try {
                $subscription = $localStripeClient->subscriptions->retrieve($this->id, [
                    'expand' => ['latest_invoice.payment_intent'],
                ]);

                return $subscription->latest_invoice;
            } catch (ApiErrorException $exception) {
                return null;
            }
        });
    }

    /**
     * Get the Stripe card details from the nested payment_method field.
     *
     * @return mixed[] The card details.
     */
    public function getCardAttribute(): ?array
    {
        return $this->getCache()->rememberForever('card', function () {
            if ($this->processor === self::PROCESSOR_BRAINTREE_LEGACY) {
                $gateway = app()->make(Gateway::class);
                try {
                    $btSubscription = $gateway->subscription()->find($this->id);
                } catch (NotFound $e) {
                    return null;
                }

                if (!$btSubscription || !isset($btSubscription->paymentMethodToken)) {
                    return null; // @codeCoverageIgnore
                }

                try {
                    $paymentMethod = $gateway->paymentMethod()->find($btSubscription->paymentMethodToken);
                } catch (NotFound $exception) {
                    return null;
                }

                if ($paymentMethod instanceof PayPalAccount) {
                    // PayPal accounts does not have cards
                    return null;
                }

                return [
                    'name'      => $paymentMethod->cardholderName,
                    'brand'     => $paymentMethod->cardType,
                    'exp_month' => $paymentMethod->expirationMonth,
                    'exp_year'  => $paymentMethod->expirationYear,
                    'last4'     => $paymentMethod->last4,
                ];
            }

            // Stripe, hot reload the client
            $localStripeClient = $this->client;

            if ($this->processor === self::PROCESSOR_STRIPE_LEGACY) {
                $localStripeClient = app()->make(StripeClientRegistry::class)->make('secondary');
            }

            try {
                $subscription = $localStripeClient->subscriptions->retrieve($this->id, [
                    'expand' => ['default_payment_method'],
                ]);
            } catch (ApiErrorException $exception) {
                return null;
            }

            $paymentMethod = $subscription->default_payment_method;

            if ($paymentMethod->type !== 'card') {
                return null; // @codeCoverageIgnore
            }

            return [
                'name'      => $paymentMethod->billing_details->name,
                'brand'     => $paymentMethod->card->brand,
                'exp_month' => str_pad((string)$paymentMethod->card->exp_month, 2, '0', STR_PAD_LEFT),
                'exp_year'  => (string)$paymentMethod->card->exp_year,
                'last4'     => $paymentMethod->card->last4,
            ];
        });
    }
}
