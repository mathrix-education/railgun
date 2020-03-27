<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * A chapter is the base wrapper of all teh Mathrix Education content.
 *
 * @property-read int                    $id
 * @property      string                 $name
 * @property      string                 $slug
 * @property      int                    $order
 * @property      bool                   $visible
 * @property      int                    $discipline_id
 * @property      int                    $grade_id
 * @property-read Carbon                 $created_at
 * @property-read Carbon                 $updated_at
 * ---
 * @property-read Grade                  $grade
 * @property-read Discipline             $discipline
 * @property-read Collection|Notion[]    $notions
 * @property-read Collection|Flashcard[] $flashcards
 * @property-read Collection|Exam[]      $exams
 * @property-read Collection|Session[]   $sessions
 */
class Chapter extends Model
{
    use Reorderable;
    use Randomizable;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'visible',
        'grade_id',
        'discipline_id',
    ];

    protected $casts = ['visible' => 'boolean'];

    protected array $orderGroups = ['discipline_id', 'grade_id'];

    /**
     * @return BelongsTo<Discipline>
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * @return BelongsTo<Grade>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * @return HasMany<Notion>
     */
    public function notions(): HasMany
    {
        return $this->hasMany(Notion::class)->orderBy('order');
    }

    /**
     * @return HasMany<Flashcard>
     */
    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class)->orderBy('order');
    }

    /**
     * @return HasMany<Exam>
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class)->orderBy('order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
