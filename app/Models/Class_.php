<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Class_
 *
 * @property int $id
 * @property string $name
 * @property string $name_short
 * @property string $slug
 * @property string $slug_short
 * @property int $order
 * @property int|null $parent_id
 * @property int $country_id
 * @property-read Country $country
 * @property-read Collection|Discipline[] $disciplines
 * @property-read Class_|null $parent
 * @property-read Collection|User[] $users
 * @method static Builder|Class_ whereCountryId($value)
 * @method static Builder|Class_ whereId($value)
 * @method static Builder|Class_ whereName($value)
 * @method static Builder|Class_ whereNameShort($value)
 * @method static Builder|Class_ whereOrder($value)
 * @method static Builder|Class_ whereParentId($value)
 * @method static Builder|Class_ whereSlug($value)
 * @method static Builder|Class_ whereSlugShort($value)
 */
class Class_ extends BaseModel
{
    /** @var string Custom table definition */
    protected $table = 'classes';

    /** @var array The fillable attributes*/
    protected $fillable = ['name', 'name_short', 'slug', 'slug_short', 'order', 'parent_id', 'country_id'];

    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'name_short' => 'required|max:255',
        'slug' => 'required|max:255',
        'slug_short' => 'required|max:255',
        'country_id' => 'required|exists:countries,id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Class_
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo('App\Models\Class_', 'parent_id');
    }

    /**
     * BelongsTo Country
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * BelongsToMany Discipline
     *
     * @return BelongsToMany
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Discipline', 'class_discipline', 'class_id');
    }

    /**
     * HasMany User
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany('App\Models\User');
    }
}