<?php
/**
 * @author Mathrix Back-End Team
 * @package App\Models
 * @since ${CARET}
 * @copyright Mathrix SA
 */


namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Score
 *
 * @author Mathrix Back-End Team
 * @package App\Models
 * @since 4.1.0
 * @copyright Mathrix SA
 * @property string $game
 * @property int $id
 * @property int|null $score
 * @property int|null $time
 * @property mixed $data
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @method static Builder|Score whereId($value)
 * @method static Builder|Score whereGame($value)
 * @method static Builder|Score whereScore($value)
 * @method static Builder|Score whereTime($value)
 * @method static Builder|Score whereData($value)
 * @method static Builder|Score whereUserId($value)
 * @method static Builder|Score whereCreatedAt($value)
 * @method static Builder|Score whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Score extends BaseModel
{
    /** @var array The guarded attributes */
    protected $fillable = ["game", "score", "time", "data", "created_at", "updated_at"];
    /** @var array The validation rules on create */
    protected $rules = [
        "game" => "required",
        "score" => "nullable|integer|min:0",
        "time" => "nullable|integer|min:0",
        "data" => "nullable|json"
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = ["data" => "array"];
    public $timestamps = true;


    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
