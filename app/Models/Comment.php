<?php
namespace App\Models;

use App\Models\Traits\Votable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;


/**
 * App\Models\Comment
 *
 * @property int $id
 * @property string|null $content
 * @property int $score
 * @property int|null $parent_id
 * @property int|null $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property int $commentable_id
 * @property string $commentable_type
 * @property-read Comment|null $parent
 * @property-read Collection|Comment[] $children
 * @property-read Model $commentable
 * @property-read User|null $user
 * @property-read Collection|Vote[] $votes
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
 * @mixin \Eloquent
 */
class Comment extends BaseModel
{
    use Votable, SoftDeletes;

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array The fillable attributes */
    protected $fillable = ["id", "content", "score", "parent_id", "user_id", "commentable_id", "commentable_type",
        "created_at", "updated_at", "deleted_at"];

    /** @var array Validation rules */
    protected $rules = [
        "content" => "required",
        "parent_id" => "nullable|exists:comments,id",
        "user_id" => "required|exists:users,id"
    ];

    /** @var array The attributes that should be mutated to dates */
    protected $dates = ["deleted_at"];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Comment
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, "parent_id");
    }


    /**
     * HasMany Comment
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, "parent_id");
    }


    /**
     * BelongsTo Comment
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
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}