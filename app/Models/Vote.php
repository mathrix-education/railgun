<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
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
class Vote extends BaseModel
{
    use UUID;

    protected $fillable = ['user_id', 'comment_id', 'value'];

    public static function booting(): void
    {
        parent::booting();

        $updateScore = static function (self $vote): void {
            DB::update(
                <<<'MYSQL'
                UPDATE comments
                SET score = (SELECT IFNULL(SUM(value), 0) FROM votes WHERE comment_id = ?) WHERE id = ?
                MYSQL,
                [$vote->comment_id, $vote->comment_id],
            );
        };

        self::saved($updateScore);
        self::deleted($updateScore);
    }

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
