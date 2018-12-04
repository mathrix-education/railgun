<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Auth;

/**
 * Model Module.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                   $id
 * @property bool                  $premium
 * @property bool                  $visible
 * @property string                $type
 * @property string                $content
 * @property array                 $questions
 * @property array                 $choices
 * @property array                 $solutions
 * @property array                 $corrections
 * @property string                $options
 * @property array                 $link
 * @property mixed                 $status
 * @property Collection|Activity[] $activities
 * @property Collection|Comment[]  $comments
 * @property Collection|Mark[]    $marks
 * @property Collection|Log[]      $logs
 * @property Collection|Product[]  $products
 * @property string|null           $mark
 * @property string                $uri_type
 */
class Module extends BaseModel
{
    public const TYPES = ["id", "course", "test", "mcq", "mcq-table", "match", "fill", "flashcard"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ["premium", "type", "content", "questions", "choices", "solutions", "corrections", "options",
        "visible", "link"];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "premium" => "boolean",
        "visible" => "boolean",
        "questions" => "array",
        "choices" => "array",
        "solutions" => "array",
        "corrections" => "array",
        "options" => "array",
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
     * Handle too array strips if user is not premium.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        /** @var User $user */
        $user = Auth::user();
        $strip = $this->premium && ($user === null || !$user->premium && !$user->isModerator());

        $keep = ["visible"];

        if ($strip) {
            foreach (array_merge($this->with, $this->appends, $this->fillable, ["comments"]) as $field) {
                if (array_key_exists($field, $array) && !\in_array($field, $keep, true)) {
                    unset($array[$field]);
                }
            }
            $array["premium"] = true;
        }

        return $array;
    }

    /*
    |--------------------------------------------------------------------------
    | Appends
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

    /**
     * Get the url-friendly module type
     *
     * @return string
     */
    public function getUriTypeAttribute(): ?string
    {
        switch ($this->type) {
            case "mcq":
                return "exercice";
            case "mcq-table":
                return "exercice-qcm";
            case "match":
                return "correspondance";
            case "test":
                return "interro";
            case "fill":
                return "texte-trou";
            case "flashcard":
                return "flashcard";
            case "course":
                return preg_match("/\[video\]([0-9A-Za-z]+)\[\/video\]/", $this->content) === 1 ? "video" : "cours";
            default:
                return null;

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Activity.
     *
     * @return BelongsToMany
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)
            ->withPivot(["order"])
            ->orderBy("pivot_order");
    }

    /**
     * HasMany Comment.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy("created_at");
    }

    /**
     * HasMany Marks.
     *
     * @return HasManyThrough
     */
    public function marks(): HasManyThrough
    {
        return $this->hasManyThrough(Mark::class, User::class);
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
}
