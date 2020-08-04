<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Content;
use Railgun\Models\Traits\Freezable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int     $id
 * @property      string  $name
 * @property      mixed[] $content
 * @property      int     $order
 * @property      bool    $premium
 * @property      bool    $visible
 * @property      int     $chapter_id
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read Chapter $chapter
 */
class Flashcard extends Model implements Content
{
    use Commentable;
    use Freezable;
    use Interactable;
    use Notable;
    use Labelable;
    use Randomizable;
    use Reorderable;

    protected       $attributes = [
        'premium' => false,
        'visible' => false,
    ];
    protected       $fillable   = [
        'name',
        'slug',
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
