<?php

namespace App\Models;

use App\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Activity.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property string $type
 * @property bool $visible
 * @property bool $premium
 * @property bool $reviewed
 * @property array $link
 * @property bool $completed
 * @property bool $favorite
 * @property bool $to_revise
 * @property Collection|Comment[] $comments
 * @property Collection|Log[] $logs
 * @property Collection|Mark[] $marks
 * @property Collection|Module[] $modules
 * @property Collection|Notion[] $notions
 */
class Activity extends BaseModel
{
    use Loggable;

    public const TYPES = ["video", "course", "test", "exercise", "summary", "flashcard"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "seo_title", "seo_description", "seo_keywords", "type", "visible",
        "premium", "reviewed"];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean",
        "options" => "array",
    ];
    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "visible" => "boolean",
        "premium" => "boolean",
    ];
    /** @var array Appends attributes */
    protected $appends = ["mark"];


    /**
     * Activity constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules["type"] = "required|in:" . implode(",", self::TYPES);

        parent::__construct($attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * BelongsToMany Module.
     *
     * @return HasMany
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy("order");
    }


    /**
     * The activity's notion.
     * @return BelongsTo
     */
    public function notion(): BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function marks()
    {
        return $this->morphMany(Mark::class, "markable");
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    /**
     * Get the mark attribute (view|to_revise|completed)
     *
     * @return string|null
     */
    public function getMarkAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        } else {
            /** @var Mark $mark */
            $mark = $this->marks()
                ->where("user_id", "=", $user->id)
                ->whereNull("module_id")
                ->orderBy("created_at", "desc")
                ->first();

            return $mark !== null ? $mark->status : null;
        }
    }
}
