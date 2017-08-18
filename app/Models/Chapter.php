<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Chapter
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $class_id
 * @property int $discipline_id
 * @property-read Class_ $class_
 * @property-read Discipline $discipline
 * @property-read Collection|Notion[] $notions
 * @method static Builder|Chapter whereClassId($value)
 * @method static Builder|Chapter whereDisciplineId($value)
 * @method static Builder|Chapter whereId($value)
 * @method static Builder|Chapter whereName($value)
 * @method static Builder|Chapter whereSlug($value)
 */
class Chapter extends BaseModel
{
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Class_
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo('App\Models\Class_');
    }

    /**
     * BelongsTo Discipline
     * @return BelongsTo
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo('App\Models\Discipline');
    }

    /**
     * HasMany Notion
     * @return HasMany
     */
    public function notions(): HasMany
    {
        return $this->hasMany('App\Models\Notion');
    }
}