<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read  int   $id
 * @property string      $name
 * @property string      $slug
 * @property int         $duration
 * @property array       $content
 * @property int         $order
 * @property bool        $visible
 * @property int         $chapter_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Exam extends Model
{
    use Interactable;
    use Randomizable;

    protected $casts = ['content' => 'array'];

    /**
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
