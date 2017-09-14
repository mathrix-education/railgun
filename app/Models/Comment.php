<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Mathrix\Lumen\Models\BaseModel;


/**
 * App\Models\Comment
 *
 * @property int $id
 * @property string|null $content
 * @property int|null $parent_id
 * @property int|null $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property int $commentable_id
 * @property string $commentable_type
 * @property-read Collection|Comment[] $children
 * @property-read Model $commentable
 * @property-read Comment|null $parent
 * @property-read User|null $user
 * @method static QueryBuilder|Comment onlyTrashed()
 * @method static Builder|Comment whereCommentableId($value)
 * @method static Builder|Comment whereCommentableType($value)
 * @method static Builder|Comment whereContent($value)
 * @method static Builder|Comment whereCreatedAt($value)
 * @method static Builder|Comment whereDeletedAt($value)
 * @method static Builder|Comment whereId($value)
 * @method static Builder|Comment whereParentId($value)
 * @method static Builder|Comment whereUpdatedAt($value)
 * @method static Builder|Comment whereUserId($value)
 * @method static QueryBuilder|Comment withTrashed()
 * @method static QueryBuilder|Comment withoutTrashed()
 * @mixin Eloquent
 */
class Comment extends BaseModel
{
    use SoftDeletes;

    /** @var array The fillable attributes*/
    protected $fillable = ['content', 'parent_id', 'user_id'];

    /** @var array Validation rules */
    protected $rules = [
        'content' => 'required',
        'user_id' => 'required|exists:user,id',
        'parent_id' => 'exists:comment,id'
    ];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array The attributes that should be mutated to dates */
    protected $dates = ['deleted_at'];


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
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * HasMany Comment
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
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
     *
     * Get all of the owning commentable models.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }


    /*
    |--------------------------------------------------------------------------
    | Overridden functions
    |--------------------------------------------------------------------------
    */
    /**
     * Override delete (also delete children))
     * @return bool|null
     */
    public function delete()
    {
        foreach ($this->children as $child) {
            $child->delete();
        }

        return parent::delete();
    }

    /**
     * Override delete (also delete children))
     * @return bool|null
     */
    public function forceDelete()
    {
        foreach ($this->children as $child) {
            $child->forceDelete();
        }

        return parent::forceDelete();
    }
}