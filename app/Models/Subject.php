<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Content;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                   $id
 * @property-read string                $name
 * @property-read string                $slug
 * @property      int                   $order
 * @property      bool                  $premium
 * @property      bool                  $visible
 * @property      int                   $notion_id
 * @property-read Carbon                $created_at
 * @property-read Carbon                $updated_at
 * ---
 * @property-read Notion                $notion
 * @property-read Collection|Exercise[] $exercises
 */
class Subject extends Model implements Content
{
    use Interactable;
    use Randomizable;
    use Reorderable;
    use Sluggable;

    protected       $fillable       = [
        'name',
        'slug',
        'order',
        'premium',
        'visible',
        'notion_id',
    ];
    protected       $casts          = [
        'premium' => 'boolean',
        'visible' => 'boolean',
    ];
    protected array $orderGroup     = ['notion_id'];
    protected array $orderRelations = [Course::class];

    public static function booting(): void
    {
        self::saving(static function (Subject $subject): void {
            if ($subject->isDirty('premium')) {
                $subject->exercises()->update(['premium' => $subject->premium]);
            }
        });
    }

    /**
     * @return BelongsTo<Notion>
     */
    public function notion(): BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }

    /**
     * @return HasMany<Exercise>
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class)->orderBy('order');
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->notion->chapter;
    }
}
