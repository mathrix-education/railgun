<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * App\Models\Country
 *
 * @property int $id
 * @property string $name
 * @property string $name_short
 * @property string $slug
 * @property string $slug_short
 * @property-read Collection|Class_[] $classes
 * @method static Builder|Country whereId($value)
 * @method static Builder|Country whereName($value)
 * @method static Builder|Country whereNameShort($value)
 * @method static Builder|Country whereSlug($value)
 * @method static Builder|Country whereSlugShort($value)
 * @mixin \Eloquent
 */
class Country extends BaseModel
{
    /** @var array The fillable attributes */
    protected $fillable = ['name', 'name_short', 'slug', 'slug_short'];

    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'name_short' => 'required|max:255',
        'slug' => 'required|max:255',
        'slug_short' => 'required|max:255',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * HasMany Class_
     *
     * @return HasMany
     */
    public function classes(): HasMany
    {
        return $this->hasMany(Class_::class);
    }

}