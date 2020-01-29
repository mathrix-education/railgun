<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int    $comment_id
 * @property-read int    $user_id
 * @property int         $value
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property User        $user
 * @property Comment     $comment
 */
class Vote extends Model
{
    use Randomizable;

    /** @var array The attributes that are mass assignable */
    protected $fillable = ['user_id', 'comment_id', 'value'];

    /**
     * BelongsTo User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * BelongsTo Comment.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
