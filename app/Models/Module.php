<?php
namespace App\Models;

use App\Models\Traits\Commentable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;


/**
 * App\Models\Module
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $premium
 * @property bool $visible
 * @property string $type
 * @property string $content
 * @property array $questions
 * @property array $choices
 * @property array $solutions
 * @property array $corrections
 * @property string $options
 * @property-read mixed $status
 * @property-read Collection|Activity[] $activities
 * @property-read Collection|Comment[] $comments
 * @property-read Collection|Log[] $logs
 * @method static Builder|Module whereId($value)
 * @method static Builder|Module whereName($value)
 * @method static Builder|Module whereDescription($value)
 * @method static Builder|Module wherePremium($value)
 * @method static Builder|Module whereVisible($value)
 * @method static Builder|Module whereType($value)
 * @method static Builder|Module whereChoices($value)
 * @method static Builder|Module whereContent($value)
 * @method static Builder|Module whereCorrections($value)
 * @method static Builder|Module whereOptions($value)
 * @method static Builder|Module whereQuestions($value)
 * @method static Builder|Module whereSlug($value)
 * @method static Builder|Module whereSolutions($value)
 * @mixin \Eloquent
 */
class Module extends BaseModel
{
    use Commentable;

    /** @var array The fillable attributes */
    protected $fillable = ["name", "slug", "description", "type", "content", "questions", "choices", "solutions",
        "corrections", "options"];

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
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "description" => "required|max:255",
        "type" => "required|in:course,fill,match,mcq,mcq-table,test,flashcard",
        "visible" => "boolean"
    ];

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
}