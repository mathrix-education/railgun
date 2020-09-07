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

/**
 * @property-read int                    $id
 * @property      string                 $name
 * @property      mixed[]                $content
 * @property      int                    $order
 * @property      bool                   $premium
 * @property      bool                   $protected
 * @property      bool                   $visible
 * @property      int                    $chapter_id
 * @property-read Carbon                 $created_at
 * @property-read Carbon                 $updated_at
 * ---
 * @property-read Chapter                $chapter
 * @property-read Collection|Flashcard[] $clones
 */
class Flashcard extends BaseModel implements BelongsToChapter, ClonableContract, LabelableContract, OwnableContract
{
    use Clonable;
    use Commentable;
    use Freezable;
    use Interactable;
    use Labelable;
    use Notable;
    use Ownable;
    use Reorderable;

    protected       $attributes = [
        'premium'   => false,
        'protected' => false,
        'visible'   => false,
    ];
    protected       $fillable   = [
        'name',
        'slug',
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

        self::created(static function (Flashcard $flashcard): void {
            if (self::$cloning) {
                return;
            }

            // Duplicate flashcard if created in a cloned chapter
            $flashcard->chapter->clones->each(static function (Chapter $clone) use ($flashcard): void {
                $flashcard->clone([
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
