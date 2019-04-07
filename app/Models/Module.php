<?php

namespace App\Models;

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
 * @property bool $visible
 * @property string $type
 * @property string $content
 * @property array $questions
 * @property array $choices
 * @property array $solutions
 * @property array $corrections
 * @property string $options
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
    public const TYPES = ["id", "course", "test", "mcq", "mcq-table", "match", "fill", "flashcard"];
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
        "seo_keywords",
        "seo_description",
        "order",
        "activity_id"
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "premium" => "boolean",
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
        "visible" => "boolean",
    ];
    /** @var array The accessors to append to the model's array form */
    protected $appends = ["mark"];


    /**
     * Module constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Dynamic rules
        $this->rules["type"] = "required|in:" . implode(",", self::TYPES);
    }


    /**
     * Allow module data striping if the user is not premium.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        /** @var User $user */
        $user = Auth::user();
        $strip = !app()->runningInConsole()
            && $this->premium
            && ($user === null || !$user->premium && !$user->tokenCan("moderator"));
        $keep = ["premium", "visible", "comments"];

        if ($strip) {
            $array = Arr::only($array, $keep);
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
