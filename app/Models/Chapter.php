<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Chapter
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property int|null $class_id
 * @property int|null $discipline_id
 * @property-read \App\Models\Class_ $class_
 * @property-read \App\Models\Discipline $discipline
 * @method static Builder|Chapter whereClassId($value)
 * @method static Builder|Chapter whereDisciplineId($value)
 * @method static Builder|Chapter whereId($value)
 * @method static Builder|Chapter whereName($value)
 * @method static Builder|Chapter whereSlug($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Notion[] $notions
 */
class Chapter extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * BelongsTo Class_
     * @return BelongsTo
     */
    public function class_()
    {
        return $this->belongsTo('App\Models\Class_');
    }

    /**
     * BelongsTo Discipline
     * @return BelongsTo
     */
    public function discipline()
    {
        return $this->belongsTo('App\Models\Discipline');
    }

    /**
     * HasMany Notion
     * @return HasMany
     */
    public function notions() {
        return $this->hasMany('App\Models\Notion');
    }
}