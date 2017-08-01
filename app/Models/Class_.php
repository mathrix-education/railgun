<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Class_
 * @property int $id
 * @property string|null $name
 * @property string|null $name_short
 * @property string|null $slug
 * @property string|null $slug_short
 * @property int|null $order
 * @property int|null $parent_id
 * @property int|null $country_id
 * @property-read Class_|null $parent
 * @property-read Country|null $country
 * @property-read Collection|Discipline[] $disciplines
 * @property-read Collection|User[] $users
 * @method static Builder|Class_ whereId($value)
 * @method static Builder|Class_ whereName($value)
 * @method static Builder|Class_ whereNameShort($value)
 * @method static Builder|Class_ whereSlug($value)
 * @method static Builder|Class_ whereSlugShort($value)
 * @method static Builder|Class_ whereOrder($value)
 * @method static Builder|Class_ whereParentId($value)
 * @method static Builder|Class_ whereCountryId($value)
 * @mixin \Eloquent
 */
class Class_ extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;
    /**
     * Custom table definition
     * @var string
     */
    protected $table = 'classes';

    /**
     * BelongsTo Class_
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo('App\Models\Class_', 'parent_id');
    }

    /**
     * BelongsTo Country
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * BelongsToMany Discipline
     * @return BelongsToMany
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Discipline', 'class_discipline', 'class_id');
    }

    /**
     * HasMany User
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany('App\Models\User');
    }
}