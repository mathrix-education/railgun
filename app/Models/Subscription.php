<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stripe\Exception\ApiErrorException;
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
 * @property      Invoice|null $latest_invoice
 * ---
 * @property-read User         $user
 */
class Subscription extends Model
{
    public            $incrementing = false;
    protected         $keyType      = 'string';
    protected         $casts        = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'canceled_at' => 'datetime',
    ];
    protected Invoice $latestInvoice;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'stripe_id');
    }

    public function userBraintree(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'braintree_id');
    }

    /**
     * Get the Stripe latest_invoice field.
     *
     * @return Invoice|null The Stripe {@see StripeSubscription} latest {@see Invoice}.
     *
     * @throws ApiErrorException
     */
    public function getLatestInvoiceAttribute(): ?Invoice
    {
        if ($this->latestInvoice) {
            return $this->latestInvoice;
        }

        if (!preg_match('/^sub_[a-zA-Z0-9]{14}$/', $this->id)) {
            return null;
        }

        $subscription = StripeSubscription::retrieve([
            'id'     => $this->id,
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        return $subscription->latest_invoice;
    }

    public function setLatestInvoiceAttribute(Invoice $invoice): void
    {
        $this->latestInvoice = $invoice;
    }
}
