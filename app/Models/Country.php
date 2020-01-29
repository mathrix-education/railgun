<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int           $id
 * @property string             $name
 * @property string             $slug
 * @property bool               $visible
 * @property string             $slug_short
 * @property-read Carbon        $created_at
 * @property-read Carbon        $updated_at
 *
 * ---
 * @property Collection|Grade[] $grades
 */
class Country extends Model
{
    use Randomizable;

    /** @var array The attributes that are mass assignable */
    protected $fillable = ['id', 'name', 'slug', 'visible'];
    protected $casts    = ['visible' => 'boolean'];

    /**
     * @return HasMany<Grade>
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
