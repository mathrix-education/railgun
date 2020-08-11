<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                $id
 * @property      string             $name
 * @property      string             $slug
 * @property      bool               $visible
 * @property-read Carbon             $created_at
 * @property-read Carbon             $updated_at
 * ---
 * @property-read Collection|Grade[] $grades
 */
class Country extends BaseModel
{
    use Sluggable;

    protected $fillable = ['name', 'slug', 'visible'];
    protected $casts    = ['visible' => 'boolean'];

    /**
     * @return HasMany<Grade>
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
