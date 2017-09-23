<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Vote
 *
 * @property int $id
 * @property int $value
 * @property int|null $user_id
 * @property string $created_at
 * @property int $votable_id
 * @property string $votable_type
 * @property-read User|null $user
 * @property-read Model $votable
 * @method static Builder|Vote whereCreatedAt($value)
 * @method static Builder|Vote whereId($value)
 * @method static Builder|Vote whereUserId($value)
 * @method static Builder|Vote whereValue($value)
 * @method static Builder|Vote whereVotableId($value)
 * @method static Builder|Vote whereVotableType($value)
 * @mixin Eloquent
 */
class Vote extends BaseModel
{
    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The fillable attributes */
    protected $fillable = ['value', 'user_id', 'votable_id', 'votable_type'];
    /** @var array Validation rules */
    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'votable_id' => 'required|integer',
        'votable_type' => 'required|in:comment'
    ];


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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * MorphTo
     */
    public function votable(): MorphTo
    {
        return $this->morphTo();
    }

}