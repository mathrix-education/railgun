<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int    $id
 * @property      string $name
 * @property      string $color
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Label extends Model
{
    public const LABELABLES = [Course::class, Exercise::class, Flashcard::class, Exam::class, Lesson::class];
}
