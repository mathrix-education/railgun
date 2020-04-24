<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int         $id
 * @property      string      $title
 * @property      string      $slug
 * @property      string|null $video_id
 * @property      array       $content
 * @property      int         $order
 * @property      bool        $visible
 * @property      int         $category_id
 * @property-read Carbon      $created_at
 * @property-read Carbon      $updated_at
 * ---
 * @property-read Category    $category
 */
class Post extends Model
{
    use Randomizable;
    use Reorderable;
    use Sluggable;

    protected       $fillable   = [
        'title',
        'slug',
        'content',
        'order',
        'visible',
        'category_id',
    ];
    protected       $casts      = ['content' => 'array'];
    protected array $orderGroup = ['category_id'];

    /**
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
