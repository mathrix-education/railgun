<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int         $id
 * @property      string      $name
 * @property      string      $slug
 * @property      string|null $image
 * @property      bool        $available
 * @property      int         $price
 * @property      int         $order
 * @property      bool        $visible
 * @property-read string      $created_at
 * @property-read string      $updated_at
 */
class Training extends Model
{
    use Acquerable;
    use Reorderable;
    use Randomizable;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'available',
        'price',
        'order',
        'visible',
    ];
    protected $casts    = [
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
}
