<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Orderable;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read  int    $id
 * @property-read  string $name
 * @property-read  string $slug
 * @property bool         $buyable
 * @property int          $price
 * @property int          $order
 * @property bool         $visible
 * @property-read string  $created_at
 * @property-read string  $updated_at
 */
class Training extends Model
{
    use Acquerable;
    use Orderable;
    use Randomizable;

    protected $casts = [
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
