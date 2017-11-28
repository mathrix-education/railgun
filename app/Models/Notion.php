<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * App\Models\Notion
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $premium
 * @property bool $visible
 * @property array $link
 * @property-read Collection|Activity[] $activities
 * @property-read Collection|Chapter[] $chapters
 * @method static Builder|Notion whereId($value)
 * @method static Builder|Notion whereName($value)
 * @method static Builder|Notion whereSlug($value)
 * @method static Builder|Notion whereOrder($value)
 * @method static Builder|Notion wherePremium($value)
 * @method static Builder|Notion whereVisible($value)
 * @mixin \Eloquent
 */
class Notion extends BaseModel
{
    public const TYPES = ["normal", "premium", "reminder", "further"];

    /** @var array The The fillable attributes */
    protected $fillable = ["id", "name", "slug", "type", "visible", "link"];

    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "chapter_id" => "required|exists:chapters,id"
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
     * BelongsToMany Activity
     *
     * @return BelongsToMany
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class);
    }


    /**
     * BelongsToMany Chapter
     *
     * @return BelongsToMany
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class);
    }
}