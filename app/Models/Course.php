<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Contracts\Clonable as ClonableContract;
use Railgun\Models\Contracts\Ownable as OwnableContract;
use Railgun\Models\Traits\Clonable;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Freezable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                 $id
 * @property      string              $name
 * @property      string              $slug
 * @property      array               $content
 * @property      int                 $order
 * @property      bool                $premium
 * @property      bool                $protected
 * @property      bool                $visible
 * @property      bool                $seo_index
 * @property      string              $seo_title
 * @property      string              $seo_description
 * @property      int                 $notion_id
 * @property-read Carbon              $created_at
 * @property-read Carbon              $updated_at
 * ---
 * @property-read Notion              $notion
 * @property-read Collection|Course[] $clones
 */
class Course extends BaseModel implements BelongsToChapter, ClonableContract, OwnableContract
{
    use Clonable;
    use Commentable;
    use Freezable;
    use Interactable;
    use Labelable;
    use Notable;
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
        'content',
        'order',
        'premium',
        'protected',
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
        'protected' => 'boolean',
        'visible'   => 'boolean',
        'seo_index' => 'boolean',
    ];
    protected array $orderGroup     = ['notion_id'];
    protected array $orderRelations = [Subject::class];
    protected array $clonable       = [
        'name',
        'slug',
        'content',
        'order',
        'premium',
        'protected',
        'visible',
        'seo_title',
        'seo_keywords',
        'seo_description',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(static function (Course $course): void {
            if (self::$cloning) {
                return;
            }

            // Duplicate course if created in a cloned notion
            $course->notion->clones->each(static function (Notion $clone) use ($course): void {
                $course->clone([
                    'notion_id' => $clone->getKey(),
                ]);
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
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->notion->chapter;
    }
}
