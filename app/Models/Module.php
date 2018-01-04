<?php
namespace App\Models;

use App\Models\Traits\Commentable;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * App\Models\Module
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
 */
class Module extends BaseModel
{
    use Commentable;

    public const TYPES = ["id", "course", "test", "mcq", "mcq-table", "match", "fill", "flashcard"];

    /** @var array The fillable attributes */
    protected $fillable = ["type", "content", "questions", "choices", "solutions", "corrections", "options", "visible",
        "link"];

    /** @var array The attributes that should be cast to native types */
    protected $casts = [
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
    protected $appends = ["status"];


    public function getStatusAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $meta = Meta::where([
            ["module_id", "=", $this->id],
            ["user_id", "=", $user->id]
        ])->first();

        return $meta === null ? null : $meta->status;
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
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class);
    }


    /**
     * HasMany Lesson
     *
     * @return HasMany
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

}
