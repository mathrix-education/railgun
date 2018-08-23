<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model Vote.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int          $id
 * @property int          $value
 * @property int|null     $user_id
 * @property int          $votable_id
 * @property string       $votable_type
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property User|null    $user
 * @property Post|Comment $votable
 *
 * @method static Builder|Vote whereId($value)
 * @method static Builder|Vote whereValue($value)
 * @method static Builder|Vote whereUserId($value)
 * @method static Builder|Vote whereVotableId($value)
 * @method static Builder|Vote whereVotableType($value)
 * @method static Builder|Vote whereUpdatedAt($value)
 * @method static Builder|Vote whereCreatedAt($value)
 * @mixin \Eloquent
 */
class Vote extends BaseModel
{
    public const VOTABLE_TYPES = ["comment"];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "value", "user_id", "votable_id", "votable_type"];
    /** @var array Validation rules */
    protected $rules = [
        "user_id" => "required|exists:users,id",
        "votable_id" => "required|integer",
        "votable_type" => "required|in:comment",
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * MorphTo.
     */
    public function votable(): MorphTo
    {
        return $this->morphTo();
    }
}
