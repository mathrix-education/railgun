<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Country.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $name_short
 * @property string $slug
 * @property string $slug_short
 * @property bool $visible
 * @property Collection|Class_[] $classes
 */
class Country extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "visible"];
    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "visible" => "boolean",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * HasMany Class_.
     *
     * @return HasMany
     */
    public function classes(): HasMany
    {
        return $this->hasMany(Class_::class);
    }
}
