<?php

namespace App\Models;

use App\Models\Traits\Votable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;

/**
 * Model Comment.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                  $id
 * @property string|null          $content
 * @property int|null             $parent_id
 * @property int|null             $user_id
 * @property int                  $path_id
 * @property int                  $module_id
 * @property int                  $score
 * @property int|null             $user_vote
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon|null          $deleted_at
 * @property User|null            $user
 * @property Path                 $path
 * @property Module               $module
 * @property Comment|null         $parent
 * @property Collection|Comment[] $children
 * @property Collection|Vote[]    $votes
 *
 * @method static QueryBuilder|Comment onlyTrashed()
 * @method static QueryBuilder|Comment withTrashed()
 * @method static QueryBuilder|Comment withoutTrashed()
 * @method static bool|null forceDelete()
 * @method static bool|null restore()
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
    protected $fillable = [
        "content",
        "score",
        "parent_id",
        "user_id",
        "module_id",
        "path_id",
        "created_at",
        "updated_at",
        "deleted_at"
    ];
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
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        /** @var Vote $potentialVote */
        $potentialVote = Vote::where([
            ["user_id", $user->id],
            ["votable_type", "comment"],
            ["votable_id", $this->id],
        ])
            ->first();

        if ($potentialVote === null) {
            return null;
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
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, "parent_id");
    }

    /**
     * HasMany Comment.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, "parent_id")
            ->orderBy("created_at");
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
}
