<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\UUID;

/**
 * @property-read string  $id
 * @property-read int     $comment_id
 * @property-read int     $user_id
 * @property      int     $value
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read User    $user
 * @property-read Comment $comment
 */
class Vote extends Model
{
    use Randomizable;
    use UUID;

    protected $fillable = ['user_id', 'comment_id', 'value'];

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Comment>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
