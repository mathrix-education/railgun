<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property      string      $id
 * @property      string      $plan
 * @property      string      $customer_id
 * @property      string      $status
 * @property      string|null $payment_intent_secret
 * @property      Carbon      $current_period_start
 * @property      Carbon      $current_period_end
 * @property      Carbon      $created_at
 * @property      Carbon      $updated_at
 * @property      Carbon|null $cancelled_at
 * ---
 * @property-read User        $user
 */
class Subscription extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $casts     = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'current_cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'stripe_id');
    }

    public function userBraintree(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'braintree_id');
    }
}
