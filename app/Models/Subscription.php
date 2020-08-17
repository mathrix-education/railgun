<?php

declare(strict_types=1);

namespace Railgun\Models;

use Braintree\CustomerSearch;
use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Billing\Stripe\Models\InteractsWithStripe;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Models\Contracts\Cacheable as CacheableContract;
use Railgun\Models\Traits\Cacheable;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\Subscription as StripeSubscription;
use function app;
use function preg_match;
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
 * ---
 * @property-read User         $user
 */
class Subscription extends BaseModel implements CacheableContract
{
    use Cacheable;
    use InteractsWithStripe;

    public const PROCESSOR_STRIPE    = 'stripe';
    public const PROCESSOR_BRAINTREE = 'braintree';

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
        if (isset($stripeSubscription->items[0]->price->lookup_key)) {
            $subscription->plan = $stripeSubscription->items[0]->price->lookup_key;
        } else {
            $modeMachine        = app()->make(ModeMachine::class);
            $subscription->plan = $modeMachine->get($stripeSubscription->plan->id)->lookup_key;
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

        /** @var string[] $statusMap The status map from Braintree <=> Stripe */
        $statusMap = [
            BraintreeSubscription::ACTIVE   => StripeSubscription::STATUS_ACTIVE,
            BraintreeSubscription::CANCELED => StripeSubscription::STATUS_CANCELED,
            BraintreeSubscription::EXPIRED  => StripeSubscription::STATUS_CANCELED,
            BraintreeSubscription::PAST_DUE => StripeSubscription::STATUS_PAST_DUE,
            BraintreeSubscription::PENDING  => StripeSubscription::STATUS_ACTIVE,
        ];
        $status    = $statusMap[$btSubscription->status];

        $customerResults = $gateway->customer()->search([
            CustomerSearch::paymentMethodToken()->is($btSubscription->paymentMethodToken),
        ]);
        $customerId      = $customerResults->getIds()[0] ?? 'unknown';

        return new self([
            'id'                   => $btSubscription->id,
            'plan'                 => $btSubscription->planId,
            'customer_id'          => $customerId,
            'status'               => $statusMap[$btSubscription->status],
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
        if ($this->processor === self::PROCESSOR_STRIPE) {
            return $this->belongsTo(User::class, 'customer_id', 'stripe_id');
        }

        return $this->belongsTo(User::class, 'customer_id', 'braintree_id');
    }

    /**
     * @return string The processor name.
     */
    public function getProcessorAttribute(): string
    {
        if (preg_match('/^sub_[a-zA-Z0-9]{14}$/', $this->id)) {
            return self::PROCESSOR_STRIPE;
        }

        return self::PROCESSOR_BRAINTREE;
    }

    /**
     * Get the Stripe latest_invoice field with the nested payment_intent.
     *
     * @return Invoice|null The Stripe {@see StripeSubscription} latest {@see Invoice}.
     */
    public function getLatestInvoiceAttribute(): ?Invoice
    {
        return $this->getCache()->rememberForever('latest_invoice', function () {
            if ($this->processor === self::PROCESSOR_BRAINTREE) {
                return null; // @codeCoverageIgnore
            }

            try {
                $subscription = $this->client->subscriptions->retrieve($this->id, [
                    'expand' => ['latest_invoice.payment_intent'],
                ]);

                return $subscription->latest_invoice;
            } catch (ApiErrorException $exception) { // @codeCoverageIgnoreStart
                return null;
            } // @codeCoverageIgnoreEnd
        });
    }

    /**
     * Get the Stripe card details from the nested payment_method field.
     *
     * @return mixed[] The card details.
     */
    public function getCardAttribute(): array
    {
        return $this->getCache()->rememberForever('card', function () {
            if ($this->processor === self::PROCESSOR_BRAINTREE) {
                return null; // @codeCoverageIgnore
            }

            try {
                $subscription = $this->client->subscriptions->retrieve($this->id, [
                    'expand' => ['default_payment_method'],
                ]);
            } catch (ApiErrorException $exception) { // @codeCoverageIgnoreStart
                return null;
            } // @codeCoverageIgnoreEnd

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
