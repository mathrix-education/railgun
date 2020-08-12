<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Railgun\Models\Contracts\Sellable;
use Railgun\Models\Traits\UUID;

/**
 * An acquisition represents the fact that a user owns a certain product.
 * This is a pivot between the users and the products table using polymorphic hasMany Eloquent relation.
 *
 * @link https://laravel.com/docs/6.x/eloquent-relationships#many-to-many-polymorphic-relations
 *
 * @property-read string      $id             The acquisition id (UUIDv4).
 * @property-read int         $user_id        The user id.
 * @property-read string      $product_type   The product type (pack, training).
 * @property-read int         $product_id     The product id.
 * @property-read string|null $transaction_id The related Stripe invoice or Braintree transaction id.
 * @property-read Carbon      $created_at     The date/time when the acquisition was created.
 * @property-read Carbon      $updated_at     The date/time when the acquisition was last updated.
 */
class Acquisition extends BaseModel
{
    use AsPivot;
    use UUID;

    protected $table    = 'acquisitions'; // required to explicitly define the join table
    protected $fillable = [
        'user_id',
        'product_type',
        'product_id',
        'transaction_id',
    ];

    /**
     * @return MorphTo<Sellable>
     */
    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
