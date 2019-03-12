<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Purchase.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.1
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property int $price
 * @property int $refund
 * @property int $user_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property Product $product
 */
class Purchase extends BaseModel
{
    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "user_id", "product_id", "created_at", "updated_at"];
    /** @var array The validation rules */
    protected $rules = [
        "price" => "required|numeric",
        "user_id" => "exists:users,id",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo User.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * BelongsTo Product.
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
