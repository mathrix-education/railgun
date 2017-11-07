<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * App\Models\Notion
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $premium
 * @property bool $visible
 * @property int $chapter_id
 * @property-read Collection|Activity[] $activities
 * @property-read Chapter $chapter
 * @method static Builder|Notion whereChapterId($value)
 * @method static Builder|Notion whereId($value)
 * @method static Builder|Notion whereName($value)
 * @method static Builder|Notion whereSlug($value)
 * @method static Builder|Notion wherePremium($value)
 * @method static Builder|Notion whereVisible($value)
 * @mixin \Eloquent
 */
class Notion extends BaseModel
{
    /** @var array The The fillable attributes */
    protected $fillable = ["name", "slug", "visible", "chapter_id", "link"];

    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "chapter_id" => "required|exists:chapters,id"
    ];


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
     * BelongsToChapter
     *
     * @return BelongsTo
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}