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
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Comment.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property string|null $content
 * @property int|null $parent_id
 * @property int|null $user_id
 * @property int $path_id
 * @property int $module_id
 * @property int $score
 * @property int|null $user_vote
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property User|null $user
 * @property Module $module
 * @property Comment|null $parent
 * @property Collection|Comment[] $children
 * @property Collection|Vote[] $votes
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

    /** @var array The default relations included */
    public $with = [
        "user:id,type,username,firstname,level,grade_id",
        "children",
        "children.user:id,type,username,firstname,level,grade_id"
    ];
    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        "content",
        "score",
        "parent_id",
        "user_id",
        "module_id",
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
     * BelongsTo Module.
     *
     * @return BelongsTo
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
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


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
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
        $potentialVote = Vote::query()
            ->where([
                ["user_id", $user->id],
                ["votable_type", "comment"],
                ["votable_id", $this->id],
            ])
            ->first();

        return $potentialVote instanceof Vote ? $potentialVote->value : null;
    }
}
