<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property Collection|Log[]      $logs
 * @property Collection|Product[]  $products
 * @property mixed                 $favorite
 * @property mixed                 $to_revise
 * @property bool                  $completed
 * @property string                $uri_front
 *
 * @method static Builder|Module whereId($value)
 * @method static Builder|Module wherePremium($value)
 * @method static Builder|Module whereVisible($value)
 * @method static Builder|Module whereType($value)
 * @method static Builder|Module whereChoices($value)
 * @method static Builder|Module whereContent($value)
 * @method static Builder|Module whereCorrections($value)
 * @method static Builder|Module whereOptions($value)
 * @method static Builder|Module whereQuestions($value)
 * @method static Builder|Module whereSolutions($value)
 *
 * @mixin \Eloquent
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
    protected $appends = ["favorite", "to_revise", "completed"];

    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules["type"] = "required|in:" . implode(",", self::TYPES);

        parent::__construct($attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | Appends
    |--------------------------------------------------------------------------
    */

    /**
     * Handle too array strips if user is not premium.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

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

    /**
     * favorite.
     *
     * @return bool|mixed|null
     */
    public function getFavoriteAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $meta = Meta::where([
            ["module_id", $this->id],
            ["user_id", $user->id],
        ])
            ->first();

        return $meta === null ? false : $meta->favorite;
    }

    /**
     * to_revise.
     *
     * @return bool
     */
    public function getToReviseAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $meta = Meta::where([
            ["module_id", $this->id],
            ["user_id", $user->id],
        ])
            ->first();

        return $meta === null ? false : $meta->to_revise;
    }

    /**
     * completed.
     *
     * @return bool
     */
    public function getCompletedAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $meta = Meta::where([
            ["module_id", $this->id],
            ["user_id", $user->id],
        ])
            ->first();

        return $meta === null ? null : $meta->completed;
    }

    /**
     * Get the url-friendly module type
     *
     * @return string
     */
    public function getUriTypeAttribute()
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
            ->withPivot(["order"]);
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
