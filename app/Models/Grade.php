<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                     $id
 * @property      string                  $name
 * @property      string                  $slug
 * @property      int                     $order
 * @property      bool                    $visible
 * @property      int|null                $parent_id
 * @property-read Carbon                  $created_at
 * @property-read Carbon                  $updated_at
 * ---
 * @property-read Grade|null              $parent
 * @property-read Collection|Grade[]      $children
 * @property-read Collection|Discipline[] $disciplines
 * @property-read Collection|Theme[]      $themes
 * @property-read Collection|User[]       $users
 */
class Grade extends BaseModel
{
    use Reorderable;
    use Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'visible',
        'parent_id',
    ];
    protected $casts    = ['visible' => 'boolean'];

    /**
     * The parent grade.
     *
     * @return BelongsTo<Grade>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The child grades.
     *
     * @return HasMany<Grade>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Theme>
     */
    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    /**
     * @return BelongsToMany<Discipline>
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class)
            ->withPivot('order')
            ->orderBy('pivot_order');
    }

    /**
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsToMany<Training>
     */
    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class);
    }
}
