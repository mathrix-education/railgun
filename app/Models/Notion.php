<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * Model Notion
 *
 * @package App\Models
 * @since 1.0.0
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $premium
 * @property bool $visible
 * @property array $link
 * @property string $type
 * @property-read Collection|Activity[] $activities
 * @property-read Collection|Chapter[] $chapters
 * @property-read Collection|Comment[] $comments
 * @method static Builder|Notion whereId($value)
 * @method static Builder|Notion whereName($value)
 * @method static Builder|Notion whereSlug($value)
 * @method static Builder|Notion whereOrder($value)
 * @method static Builder|Notion wherePremium($value)
 * @method static Builder|Notion whereVisible($value)
 * @method static Builder|Notion whereType($value)
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
     * @codeCoverageIgnore
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)->withPivot(["order"]);
    }


    /**
     * BelongsToMany Chapter
     *
     * @return BelongsToMany
     * @codeCoverageIgnore
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class)->withPivot(["order"]);
    }
}
