<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Content;
use Railgun\Models\Traits\Randomizable;
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
 */
class Notion extends Model implements Content
{
    use Randomizable;
    use Reorderable;

    public const TYPE_STANDARD = 'standard';
    public const TYPE_SUMMARY  = 'summary';
    public const TYPES         = [self::TYPE_STANDARD, self::TYPE_SUMMARY];

    protected       $fillable   = [
        'name',
        'visible',
        'order',
        'chapter_id',
    ];
    protected       $casts      = ['visible' => 'boolean'];
    protected array $orderGroup = ['chapter_id'];

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
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->chapter;
    }
}
