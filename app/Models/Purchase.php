<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Purchase.
 *
 * @since     4.0.1
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * ----
 *
 * @property int         $id
 * @property int         $price
 * @property int         $refund
 * @property int         $user_id
 * @property int         $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User        $user
 *
 * @method static Builder|Purchase whereId($value)
 * @method static Builder|Purchase wherePrice($value)
 * @method static Builder|Purchase whereRefund($value)
 * @method static Builder|Purchase whereUserId($value)
 * @method static Builder|Purchase whereProductId($value)
 * @method static Builder|Purchase whereCreatedAt($value)
 * @method static Builder|Purchase whereUpdatedAt($value)
 * @mixin \Eloquent
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
}
