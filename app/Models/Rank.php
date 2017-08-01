<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Rank
 * @property int $id
 * @property int|null $level
 * @property int|null $points_min
 * @property int|null $user_count
 * @property-read Collection|User[] $users
 * @method static Builder|Rank whereId($value)
 * @method static Builder|Rank whereLevel($value)
 * @method static Builder|Rank wherePointsMin($value)
 * @method static Builder|Rank whereUserCount($value)
 * @mixin \Eloquent
 */
class Rank extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * HasMany User
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany('App\Models\User');
    }
}