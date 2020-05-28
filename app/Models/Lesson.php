<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int     $id
 * @property      string  $name
 * @property      int     $slug
 * @property      array   $content
 * @property      int     $order
 * @property      bool    $visible
 * @property      int     $section_id
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read Section $section
 */
class Lesson extends Model
{
    use Commentable;
    use Labelable;
    use Notable;
    use Randomizable;
    use Reorderable;
    use Sluggable;

    protected        $fillable   = [
        'name',
        'slug',
        'content',
        'order',
        'visible',
        'section_id',
    ];
    protected        $casts      = [
        'content' => 'array',
        'visible' => 'boolean',
    ];
    protected array  $orderGroup = ['section_id'];

    /**
     * @return BelongsTo<Section>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
