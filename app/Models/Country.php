<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Country
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @method static Builder|Country whereId($value)
 * @method static Builder|Country whereName($value)
 * @method static Builder|Country whereSlug($value)
 * @mixin \Eloquent
 */
class Country extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

}