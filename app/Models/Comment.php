<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\DeliverCommentReplyNotifications;

/**
 * @property-read int                  $id
 * @property      string|null          $content
 * @property-read int                  $score
 * @property-read int|null             $parent_id
 * @property-read int                  $user_id
 * @property-read string               $commentable_type
 * @property-read int                  $commentable_id
 * @property-read Carbon               $created_at
 * @property-read Carbon               $updated_at
 * ---
 * @property-read Comment              $commentable
 * @property-read User                 $user
 * @property-read Comment|null         $parent
 * @property-read Vote|null            $vote
 * @property-read Collection|Comment[] $children
 * @property-read Collection|Vote[]    $votes
 */
class Comment extends BaseModel
{
    protected $fillable = [
        'content',
        'parent_id',
        'user_id',
        'commentable_type',
        'commentable_id',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(static function (Comment $comment): void {
            // Deliver comment reply notifications if the new comment is a reply.
            if ($comment->parent_id === null) {
                return;
            }

            Queue::push(new DeliverCommentReplyNotifications($comment));
        });
    }

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
     * @return MorphTo The model which own the comment.
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
        return $this->belongsTo(User::class)->withDefault(User::anonymous());
    }

    /**
     * @return HasOne<Vote>
     */
    public function vote(): HasOne
    {
        $userId = Auth::id() ?? 0;

        return $this->hasOne(Vote::class)
            ->where('user_id', '=', $userId);
    }

    /**
     * @return HasMany<Vote>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
