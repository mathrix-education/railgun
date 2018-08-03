<?php
namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Model Module
 *
 * @package App\Models
 * @since 1.0.0
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
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
 * @property array $link
 * @property-read mixed $status
 * @property-read Collection|Activity[] $activities
 * @property-read Collection|Comment[] $comments
 * @property-read Collection|Log[] $logs
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Training[] $trainings
 * @property-read mixed $favorite
 * @property-read mixed $to_revise
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[] $lessons
 * @property-read bool $completed
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
        "options" => "array"
    ];
    /** @var array The validation rules */
    protected $rules = [
        "visible" => "boolean"
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
     * Handle too array strips if user is not premium
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
                if (array_key_exists($field, $array) && !in_array($field, $keep)) {
                    unset($array[$field]);
                }
            }
            $array["premium"] = true;
        }

        return $array;
    }


    /**
     * favorite
     *
     * @return bool|mixed|null
     */
    public function getFavoriteAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $meta = Meta::where([
            ["module_id", $this->id],
            ["user_id", $user->id]
        ])->first();

        return $meta === null ? false : $meta->favorite;
    }


    /**
     * to_revise
     *
     * @return bool
     */
    public function getToReviseAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $meta = Meta::where([
            ["module_id", $this->id],
            ["user_id", $user->id]
        ])->first();

        return $meta === null ? false : $meta->to_revise;
    }


    /**
     * completed
     *
     * @return bool
     */
    public function getCompletedAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $meta = Meta::where([
            ["module_id", $this->id],
            ["user_id", $user->id]
        ])->first();

        return $meta === null ? null : $meta->completed;
    }



    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Activity
     *
     * @return BelongsToMany
     * @codeCoverageIgnore
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)->withPivot(["order"]);
    }


    /**
     * HasMany Lesson
     *
     * @return HasMany
     * @codeCoverageIgnore
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
