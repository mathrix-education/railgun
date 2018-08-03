<?php

namespace App\Models;

use App\Models\Interfaces\Purchasable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Discount
 *
 * @property string $code
 * @property int $counter
 * @property int $id
 * @property string $type
 * @property int $value
 * @property Carbon|null $expire_at
 * @property-read Collection|Product[] $products
 * @method static Builder|Discount whereId($value)
 * @method static Builder|Discount whereCode($value)
 * @method static Builder|Discount whereType($value)
 * @method static Builder|Discount whereValue($value)
 * @method static Builder|Discount whereCounter($value)
 * @method static Builder|Discount whereExpireAt($value)
 * @mixin \Eloquent
 */
class Discount extends BaseModel
{
    public const TYPES = ["absolute", "percentage"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "code", "type", "value", "counter"];
    /** @var array The attributes that should be mutated to dates */
    protected $dates = ["expire_at"];


    /**
     * BelongsToMany Product
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }


    /**
     * Check if the code is expired by its properties (counter and/or expiration date)
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->counter === 0 || $this->expire_at->isPast();
    }


    /**
     * Check if the discount is applicable on the given product
     *
     * @param Product $product
     *
     * @return bool
     */
    public function isApplicableOn(Product $product)
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->products()
            ->where("product_id", "=", $product)
            ->exists();
    }
}
