<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string      $plan
 * @property string      $customer_id
 * @property string      $status
 * @property string|null $payment_intent_secret
 * @property Carbon      $current_period_start
 * @property Carbon      $current_period_end
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $cancelled_at
 */
class Subscription extends Model
{
    /** @var bool $incrementing  */
    public $incrementing = false;
    /** @var string the primary key (id), type */
    protected $keyType = 'string';

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'current_cancelled_at' => 'datetime',
    ];
}
