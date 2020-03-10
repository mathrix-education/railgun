<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int    $id
 * @property string|null $content
 * @property-read int    $score
 * @property int|null    $parent_id
 * @property int         $user_id
 * @property string      $commentable_type
 * @property int         $commentable_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Comment extends Model
{
    use Randomizable;

    protected $fillable = [
        'content',
        'parent_id',
        'user_id',
        'commentable_type',
        'commentable_id',
    ];

    /**
     * @return BelongsTo<Comment>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('created_at');
    }

    /**
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vote(): HasOne
    {
        return $this->hasOne(Vote::class)
            ->where('user_id', '=', Auth::id());
    }

    /**
     * @return HasMany<Vote>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
