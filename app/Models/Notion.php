<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int    $id
 * @property string      $name
 * @property string      $type
 * @property bool        $visible
 * @property int         $order
 * @property int         $chapter_id
 * @property int         $activity_count
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property Chapter     $chapter
 */
class Notion extends Model
{
    use Randomizable;

    public const TYPES = ['course', 'subject'];

    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        'id',
        'name',
        'type',
        'visible',
        'order',
        'chapter_id',
    ];
    /** @var array The validation rules */
    protected $rules = [
        'name'       => 'required|max:255',
        'chapter_id' => 'required|exists:chapters,id',
    ];
    protected $casts = ['visible' => 'boolean'];
    
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
        return $this->hasMany(Course::class);
    }

    /**
     * @return HasMany<Subject>
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
}
