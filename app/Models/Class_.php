<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Class_
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property int|null $order
 * @property int|null $parent_id
 * @property int|null $country_id
 * @property-read Collection|User[] $users
 * @method static Builder|Class_ whereCountryId($value)
 * @method static Builder|Class_ whereId($value)
 * @method static Builder|Class_ whereName($value)
 * @method static Builder|Class_ whereOrder($value)
 * @method static Builder|Class_ whereParentId($value)
 * @method static Builder|Class_ whereSlug($value)
 * @mixin \Eloquent
 */
class Class_ extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'classes';

    public function parent()
    {
        $this->belongsTo('App\Models\Class_', 'parent_id');
    }

    public function country()
    {
        $this->belongsTo('App\Models\Country');
    }

    /**
     * HasMany User
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }
}