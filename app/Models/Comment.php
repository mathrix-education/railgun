<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read int             $id
 * @property string|null          $content
 * @property-read int             $score
 * @property int|null             $parent_id
 * @property int                  $user_id
 * @property string               $commentable_type
 * @property string               $commentable_id
 * @property-read Carbon          $created_at
 * @property-read Carbon          $updated_at
 */
class Comment extends Model
{
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
        'content'   => 'required',
        'parent_id' => 'nullable|exists:comments,id',
        'user_id'   => 'required|exists:users,id',
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
     * Get the votes.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * The current user vote.
     *
     * @return HasOne<Vote>
     */
    public function userVote(): HasOne
    {
        return $this->hasOne(Vote::class)->where('user_id', '=', Auth::id());
    }
}
