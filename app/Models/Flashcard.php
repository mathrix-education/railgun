<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int     $id
 * @property      array   $fronts
 * @property      array   $backs
 * @property      int     $order
 * @property      bool    $visible
 * @property      int     $chapter_id
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read Chapter $chapter
 */
class Flashcard extends Model
{
    use Commentable;
    use Interactable;
    use Notable;
    use Reorderable;
    use Randomizable;

    protected $fillable          = ['fronts', 'backs', 'order', 'visible', 'chapter_id'];
    protected $casts             = [
        'fronts' => 'array',
        'backs'  => 'array',
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
