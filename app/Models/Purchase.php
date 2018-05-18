<?php
namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Purchase
 *
 * @package App\Models
 * @property string|null $created_at
 * @property int $id
 * @property int $price
 * @property int $purchasable_id
 * @property string $purchasable_type
 * @property string|null $updated_at
 * @property int $user_id
 * @property-read User $user
 * @property-read Training|Section $purchasable
 * @method static Builder|Purchase whereCreatedAt($value)
 * @method static Builder|Purchase whereId($value)
 * @method static Builder|Purchase wherePrice($value)
 * @method static Builder|Purchase wherePurchasableId($value)
 * @method static Builder|Purchase wherePurchasableType($value)
 * @method static Builder|Purchase whereUpdatedAt($value)
 * @method static Builder|Purchase whereUserId($value)
 */
class Purchase extends BaseModel
{
    protected $with = ["purchasable"];

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function purchasable()
    {
        return $this->morphTo();
    }
}
