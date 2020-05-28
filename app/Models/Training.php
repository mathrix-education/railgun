<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                  $id
 * @property      string               $name
 * @property      string               $slug
 * @property      string|null          $image
 * @property      bool                 $available
 * @property      int                  $price
 * @property      int                  $order
 * @property      bool                 $visible
 * @property-read string               $created_at
 * @property-read string               $updated_at
 * ---
 * @property-read Collection|Pack[]    $packs
 * @property-read Collection|Section[] $sections
 * @property-read Collection|Grade[]   $grades
 */
class Training extends Model
{
    use Acquerable;
    use Reorderable;
    use Randomizable;
    use Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'duration',
        'content',
        'image',
        'available',
        'price',
        'order',
        'visible',
    ];
    protected $casts    = [
        'content'   => 'array',
        'available' => 'boolean',
        'visible'   => 'boolean',
    ];

    /**
     * @return BelongsToMany<Pack>
     */
    public function packs(): BelongsToMany
    {
        return $this->belongsToMany(Pack::class);
    }

    /**
     * @return HasMany<Section>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    /**
     * @return BelongsToMany<Grade>
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class);
    }
}
