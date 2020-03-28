<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int                            $id
 * @property      array                          $content
 * @property      string|null                    $brick_id
 * @property-read int                            $user_id
 * @property-read string                         $notable_type
 * @property-read string                         $notable_id
 * @property-read Carbon                         $created_at
 * @property-read Carbon                         $updated_at
 * @property-read Carbon                         $deleted_at
 * ---
 * @property-read Course|Exercise|Flashcard|Exam $notable
 */
class Note extends Model
{
    use SoftDeletes;
    use Randomizable;

    public const NOTABLES = [Course::class, Exercise::class, Flashcard::class, Exam::class, Lesson::class];

    protected $fillable = [
        'content',
        'brick_id',
        'user_id',
        'notable_type',
        'notable_id',
    ];
    protected $casts    = ['content' => 'array'];

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo
     */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }
}
