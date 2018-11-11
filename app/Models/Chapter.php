<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

/**
 * Model Chapter.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                 $id
 * @property string              $name
 * @property string              $slug
 * @property int                 $order
 * @property bool                $visible
 * @property int                 $class_id
 * @property int                 $discipline_id
 * @property int                 $activity_count
 * @property array               $link
 * @property Class_              $class_
 * @property Discipline          $discipline
 * @property Collection|Notion[] $notions
 */
class Chapter extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "order", "visible", "class_id", "discipline_id", "link"];
    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "class_id" => "required|exists:classes,id",
        "discipline_id" => "required|exists:disciplines,id",
        "visible" => "boolean",
    ];
    /** @var array Virtual fields */
    protected $appends = ["activity_count", "activity_to_revise", "activity_completed"];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Class_.
     *
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo(Class_::class, "class_id");
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
     * HasMany Notion.
     *
     * @return BelongsToMany
     */
    public function notions(): BelongsToMany
    {
        return $this->belongsToMany(Notion::class)
            ->withPivot(["order"])
            ->orderBy("pivot_order");
    }

    /**
     * activity_count
     *
     * @return int
     */
    public function getActivityCountAttribute()
    {
        $count = 0;
        $notions = $this->notions()->where("visible", "=", true)->get();

        foreach ($notions as $notion) {
            $notion->activities()
                ->where("visible", "=", true)
                ->get()
                ->each(function (Activity $activity) use (&$count) {
                    if ($activity->modules()->where("visible", "=", true)->count() > 0) {
                        $count++;
                    }
                });
        }

        return $count;
    }

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

        $count = Meta::where([
            ["to_revise", "=", true],
            ["user_id", "=", $user->id],
            ["chapter_id", "=", $this->id],
        ])
            ->whereNull("module_id")
            ->count();

        return $count;
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

        $count = Meta::where([
            ["completed", "=", true],
            ["user_id", "=", $user->id],
            ["chapter_id", "=", $this->id],
        ])
            ->whereNull("module_id")
            ->count();

        return $count;
    }
}
