<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Cacheable;
use Stripe\Invoice;
use Stripe\Subscription as StripeSubscription;
use function preg_match;

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
class Subscription extends Model
{
    use Cacheable;

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
        return $this->cache('latest_invoice', function () {
            $subscription = StripeSubscription::retrieve([
                'id'     => $this->id,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            return $subscription->latest_invoice;
        });
    }
}
