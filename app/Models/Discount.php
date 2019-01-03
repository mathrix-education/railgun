<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Discount.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property string               $code
 * @property int                  $counter
 * @property int                  $id
 * @property string               $type
 * @property int                  $value
 * @property Carbon|null          $expire_at
 * @property array                $link
 * @property Collection|Product[] $products
 */
class Discount extends BaseModel
{
    public const TYPES = ["absolute", "percentage"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "code", "type", "value", "counter", "expire_at", "link"];

    /** @var array The attributes that should be mutated to dates */
    protected $dates = ["expire_at"];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Product.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Check if the code is expired by its properties (counter and/or expiration date).
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->counter === 0 || ($this->expire_at !== null && $this->expire_at->isPast());
    }

    /**
     * Check if the discount is applicable on the given product.
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
            ->where("product_id", "=", $product->id)
            ->exists();
    }

    /**
     * Check if the discount is applicable on at least one of the given products.
     *
     * @param Product[] $products
     *
     * @return bool
     */
    public function isApplicableBleOnAtLeastOneOf($products)
    {
        /** @var Product $product */
        foreach ($products as $product) {
            if ($this->isApplicableOn($product)) {
                return true;
            }
        }

        return false;
    }
}
