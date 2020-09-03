<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Contracts\Clonable as ClonableContract;
use Railgun\Models\Contracts\Ownable as OwnableContract;
use Railgun\Models\Traits\Clonable;
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
class Subject extends BaseModel implements BelongsToChapter, ClonableContract, OwnableContract
{
    use Clonable;
    use Interactable;
    use Ownable;
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

        self::created(static function (Subject $subject): void {
            if (self::$cloning) {
                return;
            }

            // Duplicate subject if created in a cloned notion
            $subject->notion->clones->each(static function (Notion $clone) use ($subject): void {
                $subject->clone([
                    'notion_id' => $clone->getKey(),
                ]);
            });
        });

        self::cloned(static function (Subject $subject): void {
            $subject->unsetRelation('clones');
            $subject->load(['clones', 'exercises']);

            $subject->clones->each(static function (Subject $clone) use ($subject): void {
                $subject->exercises->each(static function (Exercise $exercise) use ($clone): void {
                    $exercise->clone(['subject_id' => $clone->getKey()]);
                });
            });
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
