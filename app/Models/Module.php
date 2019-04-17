<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Module.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property bool $premium
 * @property string $type
 * @property string $content
 * @property array $questions
 * @property array $choices
 * @property array $solutions
 * @property array $corrections
 * @property string $options
 * @property bool $visible
 * @property bool $seo_index
 * @property string $seo_title
 * @property string $seo_slug
 * @property string $seo_keywords
 * @property string $seo_description
 * @property int $order
 * @property int $activity_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property string|null $status
 * @property string|null $mark
 * ---
 * @property Activity $activity
 * @property Collection|Comment[] $comments
 * @property Collection|Log[] $logs
 * @property Collection|Mark[] $marks
 * @property Collection|Product[] $products
 * @property Collection|Report[] $reports
 */
class Module extends BaseModel
{
    public const TYPES = ["course", "test", "mcq", "flashcard"];
    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        "premium",
        "type",
        "content",
        "questions",
        "choices",
        "solutions",
        "corrections",
        "options",
        "visible",
        "seo_index",
        "seo_title",
        "seo_slug",
        "seo_keywords",
        "seo_description",
        "order",
        "activity_id"
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean",
        "questions" => "array",
        "choices" => "array",
        "solutions" => "array",
        "corrections" => "array",
        "options" => "array",
        "seo_index" => "boolean"
    ];
    /** @var array The validation rules */
    protected $rules = [
        "visible" => "required|boolean",
        "seo_index" => "required|boolean",
    ];
    /** @var array The accessors to append to the model's array form */
    protected $appends = ["mark"];


    public function getValidationRules()
    {
        // Dynamic rules
        $this->rules["type"] = "required|in:" . implode(",", self::TYPES);

        return $this->rules;
    }


    /**
     * Allow module data striping if the user is not premium.
     *
     * @return array
     */
    public function toArray()
    {
        $activityIsPremium = $this->activity !== null && $this->activity->premium;
        $this->unsetRelation("activity");
        $userIsPremium = User::isPremium();
        $testing = app()->environment() === "testing";

        $strip = !$testing && $activityIsPremium && !$userIsPremium;

        $keep = ["id", "type", "visible"];
        $array = parent::toArray();

        if ($strip) {
            $array = Arr::only($array, $keep);

            if ($this->type === "flashcard") {
                $array["questions"] = array_fill(0, count($this->questions), "");
            }
        }

        return $array;
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * @return BelongsTo
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }


    /**
     * HasMany Comment.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->orderBy("created_at");
    }


    /**
     * MorphMany Mark.
     *
     * @return MorphMany
     */
    public function marks(): MorphMany
    {
        return $this->morphMany(Mark::class, "markable");
    }


    /**
     * HasMany Product.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }


    /**
     * HasMany Post.
     *
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }


    /**
     * HasMany Report.
     *
     * @return HasMany
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }



    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    /**
     * Get the mark attribute (view|to_revise|completed)
     *
     * @return string
     */
    public function getMarkAttribute(): ?string
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        } else {
            /** @var Mark $mark */
            $mark = $this->marks()->where("user_id", "=", $user->id)->first();

            return $mark !== null ? $mark->status : null;
        }
    }
}
