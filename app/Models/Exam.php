<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read  int    $id
 * @property       string $name
 * @property       string $slug
 * @property       int    $duration
 * @property       array  $content
 * @property       int    $order
 * @property       bool   $visible
 * @property       int    $chapter_id
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read Chapter $chapter
 */
class Exam extends Model
{
    use Commentable;
    use Interactable;
    use Reorderable;
    use Randomizable;

    protected $fillable          = [
        'name',
        'slug',
        'duration',
        'content',
        'order',
        'visible',
        'chapter_id',
    ];
    protected $casts             = [
        'content' => 'array',
        'visible' => 'boolean',
    ];
    protected array $orderGroups = ['chapter_id'];

    /**
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
