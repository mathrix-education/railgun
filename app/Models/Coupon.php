<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int    $id
 * @property-read string $type
 * @property int         $value
 * @property int         $counter
 * @property Carbon      $expires_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property-read bool   $expired
 * @property-read bool   $exhausted
 * @property-read bool   $valid
 */
class Coupon extends Model
{
    use Randomizable;

    protected $fillable = ['id', 'type', 'value', 'counter', 'expires_at'];
    protected $casts    = ['expires_at' => 'datetime'];
    protected $appends  = ['expired', 'exhausted', 'valid'];

    public function getExpiredAttribute(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function getExhaustedAttribute(): bool
    {
        return $this->counter === 0;
    }

    public function getValidAttribute(): bool
    {
        return !$this->expired && !$this->exhausted;
    }
}
