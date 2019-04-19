<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Chapter.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
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
    protected $fillable = ["name", "slug", "order", "visible", "grade_id", "discipline_id"];

    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "max:255",
        "grade_id" => "required|exists:grades,id",
        "discipline_id" => "required|exists:disciplines,id",
        "visible" => "boolean",
    ];
    protected $casts = [
        "visible" => "boolean"
    ];
    /** @var array Virtual fields */
    protected $appends = ["activity_to_revise", "activity_completed"];


    /**
     * Ga chapter and its nested notions, activities and modules recursively. Return value is user-depend.
     *
     * @param int $id The chapter id
     *
     * @return Chapter
     */
    public static function recursive(int $id)
    {
        return Chapter::with([
            "notions" => function (HasMany $q) {
                $q->where("visible", "=", true);
            },
            "notions.activities" => function (HasMany $q) {
                $q->where("visible", "=", true);
            },
            "notions.activities.modules" => function (HasMany $q) {
                $q->where("visible", "=", true);
            }
        ])
            ->findOrFail($id);
    }


    /**
     * Update counters.
     */
    public function updateCounters()
    {
        $this->notion_count = $this->notions()
            ->where("visible", "=", true)
            ->count();
        $this->activity_count = $this->notions()
            ->where("visible", "=", true)
            ->sum("activity_count");
        $this->save();
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Grade.
     *
     * @return BelongsTo
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }


    /**
     * BelongsTo Discipline.
     *
     * @return BelongsTo
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }


    /**
     * The chapter notions.
     *
     * @return HasMany
     */
    public function notions(): HasMany
    {
        return $this->hasMany(Notion::class)->orderBy("order");
    }


    /**
     * HasMany Marks.
     *
     * @return HasManyThrough
     */
    public function activities(): HasManyThrough
    {
        return $this->hasManyThrough(Activity::class, Notion::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    /**
     * activity_to_revise
     *
     * @return int
     */
    public function getActivityToReviseAttribute(): ?int
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $activityIds = $this->activities()->get()->pluck("id");

        return $user->marks()
            ->where([
                ["status", "=", "to_revise"],
                ["markable_type", "=", "activity"]
            ])
            ->whereIn("markable_id", $activityIds->toArray())
            ->count();
    }


    /**
     * activity_completed
     *
     * @return int|null
     */
    public function getActivityCompletedAttribute(): ?int
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $activityIds = $this->activities()->get()->pluck("id");

        return $user->marks()
            ->where([
                ["status", "=", "completed"],
                ["markable_type", "=", "activity"]
            ])
            ->whereIn("markable_id", $activityIds->toArray())
            ->count();
    }
}
