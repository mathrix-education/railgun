<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Notion
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property int|null $chapter_id
 * @property-read Collection|Activity[] $activities
 * @method static Builder|Notion whereChapterId($value)
 * @method static Builder|Notion whereId($value)
 * @method static Builder|Notion whereName($value)
 * @method static Builder|Notion whereSlug($value)
 * @mixin \Eloquent
 */
class Notion extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * BelongsToMany Activity
     * @return BelongsToMany
     */
    public function activities()
    {
        return $this->belongsToMany('App\Models\Activity');
    }
}