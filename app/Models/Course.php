<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int    $id
 * @property      string $name
 * @property      string $slug
 * @property      array  $content
 * @property      int    $order
 * @property      bool   $visible
 * @property      bool   $seo_index
 * @property      string $seo_title
 * @property      string $seo_slug
 * @property      string $seo_description
 * @property      int    $notion_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property-read Notion $notion
 */
class Course extends Model
{
    use Interactable;
    use Randomizable;

    protected $casts = ['content' => 'array'];

    /**
     * @return BelongsTo<Notion>
     */
    public function notion(): BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }
}
