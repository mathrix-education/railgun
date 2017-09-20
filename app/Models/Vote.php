<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Vote
 *
 * @property int $id
 * @property bool $type
 * @property int|null $user_id
 * @property string $created_at
 * @property int $votable_id
 * @property string $votable_type
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereVotableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereVotableType($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $votable
 */
class Vote extends BaseModel
{

    /** @var array The fillable attributes*/
    protected $fillable = ['type', 'user_id', 'upvotable_id', 'upvotable_type'];

    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
        'user_id' => 'required|exists:users,id'
    ];

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