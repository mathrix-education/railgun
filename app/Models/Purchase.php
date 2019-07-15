<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * Class Purchase.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property int $price
 * @property string $transaction_id
 * @property int $user_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * ---
 * @property User $user
 * @property Product $product
 */
class Purchase extends BaseModel
{
    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "transaction_id", "user_id", "product_id"];
    /** @var array The validation rules */
    protected $rules = [
        "user_id" => "exists:users,id",
        "product_id" => "exists:products,id",
    ];
    protected $with = ["product"];

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
