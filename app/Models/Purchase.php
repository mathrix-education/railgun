<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Purchase
 *
 * @package App\Models
 * @since 4.0.1
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int $id
 * @property string $stripe_id
 * @property int $price
 * @property int $refund
 * @property int $purchasable_id
 * @property string $purchasable_type
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Training|Section $purchasable
 * @method static Builder|Purchase whereId($value)
 * @method static Builder|Purchase whereStripeId($value)
 * @method static Builder|Purchase wherePrice($value)
 * @method static Builder|Purchase whereRefund($value)
 * @method static Builder|Purchase whereUserId($value)
 * @method static Builder|Purchase wherePurchasableId($value)
 * @method static Builder|Purchase wherePurchasableType($value)
 * @method static Builder|Purchase whereCreatedAt($value)
 * @method static Builder|Purchase whereUpdatedAt($value)
 */
class Purchase extends BaseModel
{
    public const PURCHASABLE_TYPES = ["training", "section"];


    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "stripe_id", "price", "user_id", "purchasable_id", "purchasable_type"];
    /** @var array The validation rules */
    protected $rules = [
        "price" => "required|numeric",
        "user_id" => "exists:users,id",
        "purchasable_id" => "required|numeric"
    ];
    /** @var array Always included associations */
    protected $with = ["purchasable"];


    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules["purchasable_type"] = "required|in:" . implode(",", self::PURCHASABLE_TYPES);

        parent::__construct($attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * MorphTo Purchasable
     *
     * @return MorphTo
     */
    public function purchasable()
    {
        return $this->morphTo();
    }
}
