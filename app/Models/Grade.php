<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                $id
 * @property      string             $name
 * @property      string             $slug
 * @property      int                $order
 * @property      bool               $visible
 * @property      int|null           $parent_id
 * @property      int                $country_id
 * @property-read Carbon             $created_at
 * @property-read Carbon             $updated_at
 * ---
 * @property Country                 $country
 * @property Grade|null              $parent
 * @property Collection|Grade[]      $children
 * @property Collection|Discipline[] $disciplines
 * @property Collection|Chapter[]    $chapters
 * @property Collection|User[]       $users
 */
class Grade extends Model
{
    use Randomizable;
    use Reorderable;
    use Sluggable;

    protected       $fillable   = [
        'name',
        'slug',
        'order',
        'visible',
        'parent_id',
        'country_id',
    ];
    protected       $casts      = ['visible' => 'boolean'];
    protected array $orderGroup = ['country_id'];

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
     * @return BelongsTo<Country>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return HasMany<Chapter>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * @return BelongsToMany<Discipline>
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'chapters')
            ->groupBy(['grade_id', 'discipline_id']);
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
