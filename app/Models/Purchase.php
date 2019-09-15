<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property int         $id
 * @property int         $price
 * @property string      $transaction_id
 * @property int         $user_id
 * @property int         $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * ---
 * @property User        $user
 * @property Product     $product
 */
class Purchase extends BaseModel
{
    use AsPivot;

    /** @var string The model table. */
    protected $table = 'purchases';
    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ['id', 'transaction_id', 'user_id', 'product_id'];
    /** @var array The validation rules */
    protected $rules = [
        'user_id'    => 'exists:users,id',
        'product_id' => 'exists:products,id',
    ];
    protected $with  = ['product'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * BelongsTo Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
