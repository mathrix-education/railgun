<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property-read int    $id
 * @property      string $name
 * @property      string $color
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Label extends BaseModel
{
    public const LABELABLES = [Course::class, Exercise::class, Flashcard::class, Exam::class, Lesson::class];

    protected $fillable = ['name', 'color'];

    /**
     * @return MorphToMany<Course>
     */
    public function courses(): MorphToMany
    {
        return $this->morphedByMany(Course::class, 'labelable');
    }

    /**
     * @return MorphToMany<Exercise>
     */
    public function exercises(): MorphToMany
    {
        return $this->morphedByMany(Exercise::class, 'labelable');
    }

    /**
     * @return MorphToMany<Flashcard>
     */
    public function flashcards(): MorphToMany
    {
        return $this->morphedByMany(Flashcard::class, 'labelable');
    }

    /**
     * @return MorphToMany<Exam>
     */
    public function exams(): MorphToMany
    {
        return $this->morphedByMany(Exam::class, 'labelable');
    }
}
