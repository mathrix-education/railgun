<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Zero\Models\BaseModel;
use function implode;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $visible
 * @property bool $premium
 * @property bool $reviewed
 * @property int $notion_id
 * @property int $module_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property bool $completed
 * @property bool $favorite
 * @property bool $to_revise
 * ---
 * @property Notion $notion
 * @property Collection|Mark[] $marks
 * @property Collection|Module[] $modules
 */
class Activity extends BaseModel
{
    public const TYPES = ['video', 'course', 'test', 'exercise', 'summary', 'flashcard'];

    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        'id',
        'name',
        'type',
        'visible',
        'order',
        'premium',
        'reviewed',
        'notion_id',
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        'visible' => 'boolean',
        'premium' => 'boolean',
        'reviewed' => 'boolean',
    ];
    /** @var array The validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'visible' => 'boolean',
        'premium' => 'boolean',
        'reviewed' => 'boolean',
    ];
    /** @var array Appends attributes */
    protected $appends = ['mark'];

    /**
     * @param array $attributes
     */
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
        $this->module_count = Module::query()
            ->where('activity_id', '=', $this->id)
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
     * The activity's notion.
     */
    public function notion() : BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }

    /**
     * BelongsToMany Module.
     */
    public function modules() : HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    /**
     * Get the mark attribute (view|to_revise|completed)
     */
    public function getMarkAttribute() : ?string
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        /** @var Mark $mark */
        $mark = $this->marks()
            ->where('user_id', '=', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return $mark !== null ? $mark->status : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    public function marks() : MorphMany
    {
        return $this->morphMany(Mark::class, 'markable');
    }
}
