<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Contracts\Ownable as OwnableContract;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Freezable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

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
 * @property      string $seo_description
 * @property      int    $notion_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property-read Notion $notion
 */
class Course extends BaseModel implements BelongsToChapter, OwnableContract
{
    use Commentable;
    use Freezable;
    use Interactable;
    use Labelable;
    use Notable;
    use Ownable;
    use Reorderable;
    use Sluggable;

    protected       $attributes     = [
        'premium' => false,
        'visible' => false,
    ];
    protected       $fillable       = [
        'name',
        'slug',
        'content',
        'order',
        'premium',
        'visible',
        'seo_index',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'notion_id',
    ];
    protected       $casts          = [
        'content'   => 'array',
        'premium'   => 'boolean',
        'visible'   => 'boolean',
        'seo_index' => 'boolean',
    ];
    protected array $orderGroup     = ['notion_id'];
    protected array $orderRelations = [Subject::class];

    /**
     * @return BelongsTo<Notion>
     */
    public function notion(): BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->notion->chapter;
    }
}
