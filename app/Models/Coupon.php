<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;

/**
 * @property-read string $id
 * @property-read string $type
 * @property int         $value
 * @property int         $counter
 * @property Carbon      $expires_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property-read bool   $exhausted
 * @property-read bool   $expired
 * @property-read bool   $valid
 */
class Coupon extends BaseModel
{
    public const TYPE_ABSOLUTE = 'absolute';
    public const TYPE_PERCENT  = 'percent';

    protected $keyType      = 'string';
    public    $incrementing = false;
    protected $fillable     = ['id', 'type', 'value', 'counter', 'expires_at'];
    protected $casts        = ['expires_at' => 'datetime'];
    protected $appends      = ['expired', 'exhausted', 'valid'];

    /**
     * @return bool If the coupon is exhausted.
     */
    public function getExhaustedAttribute(): bool
    {
        return $this->counter === 0;
    }

    /**
     * @return bool If the coupon has expired.
     */
    public function getExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * @return bool If the coupon is valid and thus is applicable.
     */
    public function getValidAttribute(): bool
    {
        return !$this->exhausted && !$this->expired;
    }
}
