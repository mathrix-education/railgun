<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $order
 * @property bool $visible
 * @property int $grade_id
 * @property int $discipline_id
 * @property int $notion_count
 * @property int $activity_count
 * @property array $link
 * ---
 * @property Grade $grade
 * @property Discipline $discipline
 * @property Collection|Notion[] $notions
 * @property Collection|Activity[] $activities
 */
class Chapter extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ['name', 'slug', 'order', 'visible', 'grade_id', 'discipline_id'];

    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'max:255',
        'grade_id' => 'required|exists:grades,id',
        'discipline_id' => 'required|exists:disciplines,id',
        'visible' => 'boolean',
    ];
    protected $casts = ['visible' => 'boolean'];
    /** @var array Virtual fields */
    protected $appends = ['activity_to_revise', 'activity_completed'];

    /**
     * Get chapters in a given (grade, discipline).
     *
     * @param int $gradeId      The grade id.
     * @param int $disciplineId The discipline id.
     *
     * @return Builder
     */
    public static function byGradeAndDiscipline(int $gradeId, int $disciplineId) : Builder
    {
        return self::query()
            ->where('grade_id', '=', $gradeId)
            ->where('discipline_id', '=', $disciplineId)
            ->orderBy('order');
    }

    /**
     * Ga chapter and its nested notions, activities and modules recursively. Return value is user-depend.
     *
     * @param int $id The chapter id
     *
     * @return Chapter
     */
    public static function recursive(int $id) : Chapter
    {
        return self::with([
            'notions' => static function (HasMany $q) : void {
                $q->where('visible', '=', true);
            },
            'notions.activities' => static function (HasMany $q) : void {
                $q->where('visible', '=', true);
            },
            'notions.activities.modules' => static function (HasMany $q) : void {
                $q->where('visible', '=', true);
            },
        ])
            ->findOrFail($id);
    }

    /**
     * Update counters.
     */
    public function updateCounters() : void
    {
        $this->notion_count   = $this->notions()
            ->where('visible', '=', true)
            ->count();
        $this->activity_count = $this->notions()
            ->where('visible', '=', true)
            ->sum('activity_count');
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * The chapter notions.
     */
    public function notions() : HasMany
    {
        return $this->hasMany(Notion::class)->orderBy('order');
    }

    /**
     * BelongsTo Grade.
     */
    public function grade() : BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * BelongsTo Discipline.
     */
    public function discipline() : BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * activity_to_revise
     */
    public function getActivityToReviseAttribute() : ?int
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $activityIds = $this->activities()->get()->pluck('id');

        return $user->marks()
            ->where([
                ['status', '=', 'to_revise'],
                ['markable_type', '=', 'activity'],
            ])
            ->whereIn('markable_id', $activityIds->toArray())
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * HasMany Marks.
     */
    public function activities() : HasManyThrough
    {
        return $this->hasManyThrough(Activity::class, Notion::class);
    }

    /**
     * activity_completed
     */
    public function getActivityCompletedAttribute() : ?int
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $activityIds = $this->activities()->get()->pluck('id');

        return $user->marks()
            ->where([
                ['status', '=', 'completed'],
                ['markable_type', '=', 'activity'],
            ])
            ->whereIn('markable_id', $activityIds->toArray())
            ->count();
    }
}
