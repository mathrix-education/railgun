<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Contracts\Ownable as OwnableContract;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                   $id
 * @property-read string                $name
 * @property-read string                $slug
 * @property      int                   $order
 * @property      bool                  $premium
 * @property      bool                  $visible
 * @property      bool                  $protected
 * @property      int                   $notion_id
 * @property-read Carbon                $created_at
 * @property-read Carbon                $updated_at
 * ---
 * @property-read Notion                $notion
 * @property-read Collection|Exercise[] $exercises
 */
class Subject extends BaseModel implements BelongsToChapter, OwnableContract
{
    use Ownable;
    use Interactable;
    use Reorderable;
    use Sluggable;

    protected       $attributes     = [
        'premium'   => false,
        'protected' => false,
        'visible'   => false,
    ];
    protected       $fillable       = [
        'name',
        'slug',
        'order',
        'premium',
        'protected',
        'visible',
        'notion_id',
    ];
    protected       $casts          = [
        'premium'   => 'boolean',
        'protected' => 'boolean',
        'visible'   => 'boolean',
    ];
    protected array $orderGroup     = ['notion_id'];
    protected array $orderRelations = [Course::class];

    public static function booting(): void
    {
        self::saving(static function (Subject $subject): void {
            if ($subject->isDirty('premium')) {
                $subject->exercises()->update(['premium' => $subject->premium]);
            }

            if ($subject->isDirty('protected')) {
                $subject->exercises()->update(['protected' => $subject->protected]);
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
