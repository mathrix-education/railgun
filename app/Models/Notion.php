<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Zero\Models\BaseModel;
use function implode;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $visible
 * @property int $order
 * @property int $chapter_id
 * @property int $activity_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property Collection|Chapter $chapter
 * @property Collection|Activity[] $activities
 * @property Collection|Comment[] $comments
 */
class Notion extends BaseModel
{
    public const TYPES = ['normal', 'premium', 'reminder', 'further'];

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
        'name' => 'required|max:255',
        'chapter_id' => 'required|exists:chapters,id',
    ];
    protected $casts = ['visible' => 'boolean'];

    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules['type'] = 'required|in:' . implode(',', self::TYPES);

        parent::__construct($attributes);
    }

    /**
     * Update counters.
     */
    public function updateCounters() : void
    {
        $this->activity_count = Activity::query()
            ->where('notion_id', '=', $this->id)
            ->where('visible', '=', true)
            ->count();
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * The notion parent chapter.
     */
    public function chapter() : BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * The notion child activities.
     */
    public function activities() : HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('order');
    }
}
