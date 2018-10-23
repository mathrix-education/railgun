<?php

namespace App\Models;

use App\Models\Interfaces\Commentable;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\Loggable;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Activity.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                  $id
 * @property string               $name
 * @property string               $slug
 * @property string|null          $seo_title
 * @property string|null          $seo_description
 * @property string               $type
 * @property bool                 $visible
 * @property bool                 $premium
 * @property array                $link
 * @property bool                 $completed
 * @property bool                 $favorite
 * @property bool                 $to_revise
 * @property Collection|Comment[] $comments
 * @property Collection|Log[]     $logs
 * @property Collection|Module[]  $modules
 * @property Collection|Notion[]  $notions
 */
class Activity extends BaseModel implements Commentable
{
    use Loggable, CommentableTrait;

    public const TYPES = ["video", "course", "test", "exercise", "summary", "flashcard"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "seo_title", "seo_description", "seo_keywords", "type", "visible",
        "premium", "link"];
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
        return $this->belongsToMany(Module::class)
            ->withPivot(["order"])
            ->orderBy("pivot_order");
    }

    /**
     * BelongsToMany Notion.
     *
     * @return BelongsToMany
     */
    public function notions(): BelongsToMany
    {
        return $this->belongsToMany(Notion::class)
            ->withPivot(["order"])
            ->orderBy("pivot_order");
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
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
        ])
            ->first();

        return $meta === null ? false : (bool) $meta->favorite;
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
        ])
            ->first();

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
        ])
            ->first();

        return $meta === null ? null : $meta->completed;
    }
}
