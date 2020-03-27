<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read               int    $id
 * @property                    string $name
 * @property                    string $slug
 * @property                    bool   $beta
 * @property-read               Carbon $created_at
 * @property-read               Carbon $updated_at
 * ---
 * @property Collection|Grade[]        $grades
 * @property Collection|Chapter[]      $chapters
 * @property Collection|Session[]      $sessions
 */
class Discipline extends Model
{
    use Randomizable;

    protected $fillable = ['id', 'name', 'slug', 'beta'];

    protected $casts = [
        'visible' => 'boolean',
        'beta'    => 'boolean',
    ];

    /**
     * @return BelongsToMany<Grade>
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'chapters')
            ->groupBy(['grade_id', 'discipline_id']);
    }

    /**
     * @return HasMany<Chapter>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
