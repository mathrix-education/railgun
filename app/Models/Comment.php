<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
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
 * @method static QueryBuilder|Comment onlyTrashed()
 * @method static QueryBuilder|Comment withTrashed()
 * @method static QueryBuilder|Comment withoutTrashed()
 * @method static bool|null forceDelete()
 * @method static bool|null restore()
 */
class Comment extends BaseModel
{
    use SoftDeletes;

    /** @var array The default relations included */
    public $with = ['user:id,username,firstname,grade_id'];
    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        'content',
        'score',
        'parent_id',
        'user_id',
        'module_id',
        'deleted_at',
    ];
    /** @var array Validation rules */
    protected $rules = [
        'content' => 'required',
        'parent_id' => 'nullable|exists:comments,id',
        'user_id' => 'required|exists:users,id',
    ];
    /** @var array The attributes that should be mutated to dates */
    protected $dates = ['deleted_at'];
    /** @var array Virtual fields */
    protected $appends = ['score', 'user_vote'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * HasMany Comment.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('created_at');
    }

    /**
     * BelongsTo Module.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * BelongsTo Comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Score attribute.
     */
    public function getScoreAttribute(): int
    {
        return (int)$this->votes()->sum('value');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * Get the votes.
     *
     * @codeCoverageIgnore
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the user vote value (0|1|-1).
     */
    public function getUserVoteAttribute(): int
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return 0;
        }

        $query = $this->votes()->where('user_id', '=', $user->id);

        if (! $query->exists()) {
            return 0;
        }

        /** @var Vote $vote */
        $vote = $query->firstOrFail();

        return $vote->value;
    }
}
