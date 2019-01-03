<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model Notion.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int                   $id
 * @property string                $name
 * @property string                $slug
 * @property bool                  $premium
 * @property bool                  $visible
 * @property array                 $link
 * @property string                $type
 * @property Collection|Activity[] $activities
 * @property Collection|Chapter[]  $chapters
 * @property Collection|Comment[]  $comments
 */
class Notion extends BaseModel
{
    public const TYPES = ["normal", "premium", "reminder", "further"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "type", "visible", "link"];
    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "chapter_id" => "required|exists:chapters,id",
    ];

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
     * BelongsToMany Chapter.
     *
     * @return BelongsToMany
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class)
            ->withPivot(["order"])
            ->orderBy("pivot_order");
    }

    /**
     * HasMany Marks.
     *
     * @return HasManyThrough
     */
    public function marks(): HasManyThrough
    {
        return $this->hasManyThrough(Mark::class, Path::class);
    }

}
