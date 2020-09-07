<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Contracts\Clonable as ClonableContract;
use Railgun\Models\Contracts\Labelable as LabelableContract;
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
 * @property-read int               $id
 * @property      string            $name
 * @property      string            $slug
 * @property      int               $duration
 * @property      array             $content
 * @property      int               $order
 * @property      bool              $premium
 * @property      bool              $protected
 * @property      bool              $visible
 * @property      int               $chapter_id
 * @property-read Carbon            $created_at
 * @property-read Carbon            $updated_at
 * ---
 * @property-read Chapter           $chapter
 * @property-read Collection|Exam[] $clones
 */
class Exam extends BaseModel implements BelongsToChapter, ClonableContract, LabelableContract, OwnableContract
{
    use Clonable;
    use Commentable;
    use Freezable;
    use Interactable;
    use Labelable;
    use Ownable;
    use Notable;
    use Reorderable;
    use Sluggable;

    protected       $attributes = [
        'premium'   => false,
        'protected' => false,
        'visible'   => false,
    ];
    protected       $fillable   = [
        'name',
        'slug',
        'duration',
        'content',
        'order',
        'premium',
        'protected',
        'visible',
        'chapter_id',
    ];
    protected       $casts      = [
        'content'   => 'array',
        'premium'   => 'boolean',
        'protected' => 'boolean',
        'visible'   => 'boolean',
    ];
    protected array $orderGroup = ['chapter_id'];

    public static function boot(): void
    {
        parent::boot();

        self::created(static function (Exam $exam): void {
            if (self::$cloning) {
                return;
            }

            // Duplicate exam if created in a cloned chapter
            $exam->chapter->clones->each(static function (Chapter $clone) use ($exam): void {
                $exam->clone([
                    'chapter_id' => $clone->getKey(),
                ]);
            });
        });
    }

    /**
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->chapter;
    }
}
