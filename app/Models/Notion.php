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
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int                  $id
 * @property      string               $name
 * @property      bool                 $visible
 * @property      int                  $order
 * @property      int                  $chapter_id
 * @property-read Carbon               $created_at
 * @property-read Carbon               $updated_at
 * ---
 * @property-read Chapter              $chapter
 * @property-read Collection|Course[]  $courses
 * @property-read Collection|Subject[] $subjects
 * @property-read Collection|Problem[] $problems
 * @property-read Collection|Notion[]  $clones
 */
class Notion extends BaseModel implements BelongsToChapter, ClonableContract, OwnableContract
{
    use Clonable;
    use Ownable;
    use Reorderable;

    public const TYPE_STANDARD = 'standard';
    public const TYPE_SUMMARY  = 'summary';
    public const TYPES         = [self::TYPE_STANDARD, self::TYPE_SUMMARY];

    protected       $fillable   = [
        'type',
        'name',
        'visible',
        'order',
        'chapter_id',
    ];
    protected       $casts      = ['visible' => 'boolean'];
    protected array $orderGroup = ['chapter_id', 'type'];

    public static function boot(): void
    {
        parent::boot();

        self::created(static function (Notion $notion): void {
            if (self::$cloning) {
                return;
            }

            // Duplicate notion if created in a cloned chapter
            $notion->chapter->clones->each(static function (Chapter $clone) use ($notion): void {
                $notion->clone([
                    'chapter_id' => $clone->getKey(),
                ]);
            });
        });

        self::cloned(static function (Notion $notion): void {
            $notion->unsetRelation('clones'); // ensure we got a fresh clones
            $notion->load(['clones', 'courses', 'subjects']);

            $notion->clones->each(static function (Notion $clone) use ($notion): void {
                $notion->courses->each(static function (Course $course) use ($clone): void {
                    $course->clone(['notion_id' => $clone->getKey()]);
                });

                $notion->subjects->each(static function (Subject $subject) use ($clone): void {
                    $subject->clone(['notion_id' => $clone->getKey()]);
                });
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
     * @return HasMany<Course>
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('order');
    }

    /**
     * @return HasMany<Subject>
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('order');
    }

    /**
     * @return HasMany<Problem>
     */
    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class)->orderBy('order');
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->chapter;
    }
}
