<?php

namespace App\Models;

use App\Models\Traits\Votable;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Model Comment.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * ----
 *
 * @property int                  $id
 * @property string|null          $content
 * @property int|null             $parent_id
 * @property int|null             $user_id
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon|null          $deleted_at
 * @property int                  $commentable_id
 * @property string               $commentable_type
 * @property int                  $score
 * @property int|null             $user_vote
 * @property User|null            $user
 * @property BaseModel            $commentable
 * @property Comment|null         $parent
 * @property Collection|Comment[] $children
 * @property Collection|Vote[]    $votes
 *
 * @method static Builder|Comment whereId($value)
 * @method static Builder|Comment whereContent($value)
 * @method static Builder|Comment whereScore($value)
 * @method static Builder|Comment whereUserId($value)
 * @method static Builder|Comment whereParentId($value)
 * @method static Builder|Comment whereCreatedAt($value)
 * @method static Builder|Comment whereUpdatedAt($value)
 * @method static Builder|Comment whereDeletedAt($value)
 * @method static Builder|Comment whereCommentableId($value)
 * @method static Builder|Comment whereCommentableType($value)
 * @method static QueryBuilder|Comment onlyTrashed()
 * @method static QueryBuilder|Comment withTrashed()
 * @method static QueryBuilder|Comment withoutTrashed()
 * @method static bool|null forceDelete()
 * @method static bool|null restore()
 * @mixin \Eloquent
 */
class Comment extends BaseModel
{
    use Votable, SoftDeletes;

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The default relations included */
    public $with = [
        "user:id,type,username,firstname,level,class_id",
        "children",
        "children.user:id,type,username,firstname,level,class_id",
    ];
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "content", "score", "parent_id", "user_id", "commentable_id", "commentable_type",
        "created_at", "updated_at", "deleted_at", ];
    /** @var array Validation rules */
    protected $rules = [
        "content" => "required",
        "parent_id" => "nullable|exists:comments,id",
        "user_id" => "required|exists:users,id",
    ];
    /** @var array The attributes that should be mutated to dates */
    protected $dates = ["deleted_at"];
    /** @var array Virtual fields */
    protected $appends = ["score", "user_vote"];

    /**
     * Get the user vote value (null|1|-1).
     *
     * @return int|null
     */
    public function getUserVoteAttribute()
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        /** @var Vote $potentialVote */
        $potentialVote = Vote::where([
            ["user_id", $user->id],
            ["votable_type", "comment"],
            ["votable_id", $this->id],
        ])->first();

        if ($potentialVote === null) {
            return;
        }

        return $potentialVote->value;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Comment.
     *
     * @return BelongsTo
     * @codeCoverageIgnore
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, "parent_id");
    }

    /**
     * HasMany Comment.
     *
     * @return HasMany
     * @codeCoverageIgnore
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, "parent_id");
    }

    /**
     * BelongsTo Comment.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
