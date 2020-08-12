<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Freezable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int     $id
 * @property      string  $name
 * @property      string  $slug
 * @property      int     $duration
 * @property      array   $content
 * @property      int     $order
 * @property      bool    $premium
 * @property      bool    $visible
 * @property      int     $chapter_id
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read Chapter $chapter
 */
class Exam extends BaseModel implements BelongsToChapter
{
    use Commentable;
    use Freezable;
    use Interactable;
    use Labelable;
    use Notable;
    use Reorderable;
    use Sluggable;

    protected       $attributes = [
        'premium' => false,
        'visible' => false,
    ];
    protected       $fillable   = [
        'name',
        'slug',
        'duration',
        'content',
        'order',
        'premium',
        'visible',
        'chapter_id',
    ];
    protected       $casts      = [
        'content' => 'array',
        'premium' => 'boolean',
        'visible' => 'boolean',
    ];
    protected array $orderGroup = ['chapter_id'];

    /**
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->chapter;
    }
}
