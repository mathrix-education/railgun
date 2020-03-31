<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\UUID;

/**
 * @property-read string $id
 * @property-read int    $user_id
 * @property-read int    $discipline_id
 * @property-read int    $chapter_id
 * @property-read Carbon $updated_at
 */
class Session extends Model
{
    use Randomizable;
    use UUID;

    public $timestamps  = false;
    protected $fillable = [
        'user_id',
        'discipline_id',
        'chapter_id',
        'updated_at',
    ];

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Discipline>
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
