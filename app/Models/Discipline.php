<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                  $id
 * @property      string               $name
 * @property      string               $slug
 * @property      bool                 $beta
 * @property-read Carbon               $created_at
 * @property-read Carbon               $updated_at
 * ---
 * @property-read Collection|Grade[]   $grades
 * @property-read Collection|Theme[]   $themes
 * @property-read Collection|Session[] $sessions
 */
class Discipline extends Model
{
    use Randomizable;
    use Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'beta',
    ];
    protected $casts    = [
        'visible' => 'boolean',
        'beta'    => 'boolean',
    ];

    /**
     * @return BelongsToMany<Grade>
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'themes')
            ->groupBy(['grade_id', 'discipline_id']);
    }

    /**
     * @return HasMany<Theme>
     */
    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    /**
     * @return HasMany<Session>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
