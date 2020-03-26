<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Lockable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int    $id
 * @property      string $name
 * @property      string $slug
 * @property      array  $content
 * @property      int    $order
 * @property      bool   $premium
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
    use Commentable;
    use Interactable;
    use Notable;
    use Lockable;
    use Reorderable;
    use Randomizable;

    protected $fillable = [
        'name',
        'slug',
        'content',
        'order',
        'premium',
        'visible',
        'seo_index',
        'seo_title',
        'seo_slug',
        'seo_description',
        'notion_id',
    ];

    protected $casts = [
        'content'   => 'array',
        'visible'   => 'boolean',
        'seo_index' => 'boolean',
    ];

    protected array $orderGroups = ['notion_id'];

    /**
     * @return BelongsTo<Notion>
     */
    public function notion(): BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }

    /**
     * @return array the content Silicate wall.
     */
    public function getContentAttribute(): array
    {
        return $this->lock('content', []);
    }
}
