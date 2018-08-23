<?php

namespace App\Models;

use App\Models\Interfaces\Commentable;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\Loggable;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Activity.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * ----
 *
 * @property int                  $id
 * @property string               $name
 * @property string               $slug
 * @property string|null          $name_seo
 * @property string               $type
 * @property bool                 $visible
 * @property array                $link
 * @property bool                 $completed
 * @property bool                 $favorite
 * @property bool                 $to_revise
 * @property Collection|Comment[] $comments
 * @property Collection|Log[]     $logs
 * @property Collection|Module[]  $modules
 * @property Collection|Notion[]  $notions
 *
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereName($value)
 * @method static Builder|Activity whereSlug($value)
 * @method static Builder|Activity whereNameSeo($value)
 * @method static Builder|Activity whereType($value)
 * @method static Builder|Activity whereVisible($value)
 * @mixin \Eloquent
 */
class Activity extends BaseModel implements Commentable
{
    use Loggable, CommentableTrait;

    public const TYPES = ["video", "course", "test", "exercise", "summary", "flashcard"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "name_seo", "type", "visible", "link"];
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
    ];
    /** @var array Appends attributes */
    protected $appends = ["favorite", "to_revise", "completed"];

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
     * @return BelongsToMany
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)->withPivot(["order"]);
    }

    /**
     * BelongsToMany Notion.
     *
     * @return BelongsToMany
     */
    public function notions(): BelongsToMany
    {
        return $this->belongsToMany(Notion::class)->withPivot(["order"]);
    }

    /*
    |--------------------------------------------------------------------------
    | Appends fields
    |--------------------------------------------------------------------------
    */

    /**
     * favorite.
     *
     * @return bool
     */
    public function getFavoriteAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        $meta = Meta::where([
            ["activity_id", $this->id],
            ["user_id", $user->id],
        ])->first();

        return $meta === null ? false : (bool)$meta->favorite;
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
            ["activity_id", $this->id],
            ["user_id", $user->id],
        ])->first();

        return $meta === null ? null : $meta->to_revise;
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
            ["activity_id", $this->id],
            ["user_id", $user->id],
        ])->first();

        return $meta === null ? null : $meta->completed;
    }
}
